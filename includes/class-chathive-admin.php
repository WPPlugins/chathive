<?php
/**
 * The settings screen: Settings, then ChatHive.
 *
 * @package ChatHive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lets the site owner enter the Site ID, choose where the chat shows and test
 * the connection.
 */
final class ChatHive_Admin {

	const PAGE         = 'chathive';
	const TEST_ACTION  = 'chathive_test';
	const RESULT_TRANS = 'chathive_test_result_';

	/**
	 * Hooks the screen into the admin.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_text' ) );
		add_action( 'admin_post_' . self::TEST_ACTION, array( __CLASS__, 'handle_test' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CHATHIVE_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Adds Settings, then ChatHive.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page(
			__( 'ChatHive', 'chathive' ),
			__( 'ChatHive', 'chathive' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Registers the one option and how it is cleaned.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			'chathive',
			ChatHive_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => ChatHive_Settings::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Cleans the submitted settings and says so when the Site ID was not valid.
	 *
	 * @param mixed $input Submitted values.
	 * @return array{site_id: string, mode: string}
	 */
	public static function sanitize( $input ) {
		$clean     = ChatHive_Settings::clean( $input );
		$submitted = is_array( $input ) && isset( $input['site_id'] ) ? trim( (string) $input['site_id'] ) : '';
		if ( '' !== $submitted && '' === $clean['site_id'] ) {
			add_settings_error(
				ChatHive_Settings::OPTION,
				'chathive_site_id',
				__( 'That does not look like a ChatHive Site ID. It starts with "site_". Copy it again from ChatHive (Setup, then Widget).', 'chathive' )
			);
		}
		return $clean;
	}

	/**
	 * Adds a link to the settings on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'chathive' ) . '</a>';
		array_unshift( $links, $settings );
		return $links;
	}

	/**
	 * Suggests wording for the site's privacy policy (Settings, then Privacy).
	 *
	 * @return void
	 */
	public static function add_privacy_text() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$text  = '<p class="privacy-policy-tutorial">' . esc_html__( 'This site uses the ChatHive chatbot. Mention it in your privacy policy.', 'chathive' ) . '</p>';
		$text .= '<p>' . sprintf(
			/* translators: 1: ChatHive privacy policy link. */
			esc_html__( 'Our website has a chatbot provided by ChatHive. When you open it, your browser contacts ChatHive. If you write to the chatbot, your messages and any contact details you give are processed by ChatHive for us, and your browser time zone is sent so meeting times read correctly. The chat widget does not set cookies or store anything in your browser. Read the ChatHive privacy policy: %1$s', 'chathive' ),
			'<a href="' . esc_url( ChatHive_Settings::app_url() . '/privacy' ) . '">' . esc_html( ChatHive_Settings::app_url() . '/privacy' ) . '</a>'
		) . '</p>';
		wp_add_privacy_policy_content( 'ChatHive', wp_kses_post( $text ) );
	}

	/**
	 * Tests the connection when the owner presses the button, then returns to
	 * the settings screen with the result.
	 *
	 * @return void
	 */
	public static function handle_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'chathive' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::TEST_ACTION );

		set_transient(
			self::RESULT_TRANS . get_current_user_id(),
			self::test_connection(),
			5 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( admin_url( 'options-general.php?page=' . self::PAGE ) );
		exit;
	}

	/**
	 * Asks the ChatHive service to start the widget for this site, as a visitor's
	 * browser would, and explains the answer. It is marked as a test, so it does
	 * not count as the widget having loaded on a page.
	 *
	 * @return array{ok: bool, message: string}
	 */
	public static function test_connection() {
		$settings = ChatHive_Settings::get();
		if ( '' === $settings['site_id'] ) {
			return array(
				'ok'      => false,
				'message' => __( 'Enter and save your Site ID first.', 'chathive' ),
			);
		}

		$domain   = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$response = wp_remote_post(
			ChatHive_Settings::api_url() . '/widget/init',
			array(
				'timeout' => 10,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'siteId' => $settings['site_id'],
						'domain' => $domain,
						'probe'  => true,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'ok'      => false,
				'message' => __( 'Could not reach ChatHive from this site. Check that the server can make outgoing requests, then try again.', 'chathive' ),
			);
		}

		return self::explain( (int) wp_remote_retrieve_response_code( $response ), (string) wp_remote_retrieve_body( $response ), $domain );
	}

	/**
	 * Turns the service's answer into a sentence for the site owner.
	 *
	 * @param int    $status HTTP status.
	 * @param string $body   Response body (JSON).
	 * @param string $domain This site's domain.
	 * @return array{ok: bool, message: string}
	 */
	public static function explain( $status, $body, $domain ) {
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( $status >= 200 && $status < 300 && ! empty( $data['widgetToken'] ) ) {
			$name = isset( $data['robotName'] ) && is_string( $data['robotName'] ) && '' !== $data['robotName'] ? $data['robotName'] : '';
			return array(
				'ok'      => true,
				'message' => '' !== $name
					/* translators: %s: the chatbot's name. */
					? sprintf( __( 'Connected. Your chatbot "%s" will answer visitors on this site.', 'chathive' ), $name )
					: __( 'Connected. Create your chatbot in ChatHive (Setup, then Chat) so visitors have something to talk to.', 'chathive' ),
			);
		}

		$code = '';
		if ( isset( $data['errors'] ) && is_array( $data['errors'] ) && isset( $data['errors'][0] ) && is_string( $data['errors'][0] ) ) {
			$code = $data['errors'][0];
		}

		if ( 'INVALID_SITE_ID' === $code ) {
			return array(
				'ok'      => false,
				'message' => __( 'ChatHive does not know this Site ID. Copy it again from ChatHive (Setup, then Widget).', 'chathive' ),
			);
		}
		if ( 'DOMAIN_NOT_ALLOWED' === $code ) {
			return array(
				'ok'      => false,
				'message' => sprintf(
					/* translators: %s: this website's domain. */
					__( 'This Site ID belongs to a company whose landing page is on another address. In ChatHive (Setup, then Details) set the landing page to this site: %s', 'chathive' ),
					$domain
				),
			);
		}
		if ( 'SUBSCRIPTION_INACTIVE' === $code ) {
			return array(
				'ok'      => false,
				'message' => __( 'The ChatHive account for this Site ID has no active subscription. Open ChatHive to subscribe.', 'chathive' ),
			);
		}
		if ( 429 === $status ) {
			return array(
				'ok'      => false,
				'message' => __( 'Too many tests in a short time. Wait a minute and try again.', 'chathive' ),
			);
		}

		return array(
			'ok'      => false,
			'message' => __( 'ChatHive could not start the chat. Try again in a moment; if it keeps happening, write to contact@chathive.ca.', 'chathive' ),
		);
	}

	/**
	 * Prints the settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = ChatHive_Settings::get();
		$app_url    = ChatHive_Settings::app_url();
		$result_key = self::RESULT_TRANS . get_current_user_id();
		$result     = get_transient( $result_key );
		if ( false !== $result ) {
			delete_transient( $result_key );
		}
		$field = ChatHive_Settings::OPTION;
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'ChatHive', 'chathive' ); ?></h1>
			<p>
				<?php echo esc_html__( 'ChatHive is an AI chatbot that answers your visitors from your own documents, collects their contact details and books meetings. It needs a ChatHive account.', 'chathive' ); ?>
			</p>

			<?php if ( is_array( $result ) && isset( $result['ok'], $result['message'] ) ) : ?>
				<div class="notice <?php echo $result['ok'] ? 'notice-success' : 'notice-error'; ?>">
					<p><?php echo esc_html( (string) $result['message'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! ChatHive_Settings::is_configured() ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php
						printf(
							/* translators: 1: link to create a ChatHive account. */
							esc_html__( 'The chat is not on your site yet. No account? %1$s (14 days free, no card). Then paste your Site ID below.', 'chathive' ),
							'<a href="' . esc_url( $app_url . '/signup' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Create your ChatHive account', 'chathive' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'chathive' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="chathive_site_id"><?php echo esc_html__( 'Site ID', 'chathive' ); ?></label></th>
						<td>
							<input name="<?php echo esc_attr( $field ); ?>[site_id]" id="chathive_site_id" type="text" class="regular-text code" value="<?php echo esc_attr( $settings['site_id'] ); ?>" placeholder="site_xxxxxxxx" autocomplete="off" spellcheck="false" />
							<p class="description">
								<?php
								printf(
									/* translators: 1: link to the ChatHive widget settings. */
									esc_html__( 'Find it in ChatHive: %1$s, tab Widget.', 'chathive' ),
									'<a href="' . esc_url( $app_url . '/setup' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Setup', 'chathive' ) . '</a>'
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Where to show the chat', 'chathive' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr( $field ); ?>[mode]" value="<?php echo esc_attr( ChatHive_Settings::MODE_ALL ); ?>" <?php checked( $settings['mode'], ChatHive_Settings::MODE_ALL ); ?> />
									<?php echo esc_html__( 'On every page', 'chathive' ); ?>
								</label><br />
								<label>
									<input type="radio" name="<?php echo esc_attr( $field ); ?>[mode]" value="<?php echo esc_attr( ChatHive_Settings::MODE_SELECTED ); ?>" <?php checked( $settings['mode'], ChatHive_Settings::MODE_SELECTED ); ?> />
									<?php echo esc_html__( 'Only on pages where I add the ChatHive Chat block or the [chathive] shortcode', 'chathive' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<?php if ( ChatHive_Settings::is_configured() ) : ?>
				<h2><?php echo esc_html__( 'Check the connection', 'chathive' ); ?></h2>
				<p><?php echo esc_html__( 'Asks ChatHive to start the chat for this site, the way a visitor\'s browser would. Your ChatHive landing page must be this site\'s address.', 'chathive' ); ?></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::TEST_ACTION ); ?>" />
					<?php wp_nonce_field( self::TEST_ACTION ); ?>
					<?php submit_button( __( 'Test connection', 'chathive' ), 'secondary', 'submit', false ); ?>
				</form>
				<p>
					<a href="<?php echo esc_url( $app_url . '/setup' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Open ChatHive to add topics, see leads and manage your plan', 'chathive' ); ?></a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
