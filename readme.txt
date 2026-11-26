=== ChatHive – AI Chatbot for your website ===
Contributors: chathive
Tags: chatbot, ai, chat, customer support, lead generation
Requires at least: 6.3
Tested up to: 7.2
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Put your ChatHive AI chatbot on your WordPress site. It answers visitors from your own documents, collects leads and books meetings.

== Description ==

ChatHive is an AI chatbot service for small businesses. You give it your own documents (a price list, a menu, a return policy, your landing page) and it answers your visitors from them, in your tone and your language. When a visitor leaves their details it saves them as a lead, and it can book a meeting on your calendar or your team's.

This plugin puts the ChatHive chat on your WordPress site. You do not need to edit any code: paste your ChatHive Site ID and the chat appears at the bottom of your pages.

The plugin is free and open source. ChatHive itself is a hosted service: it needs an account, and it has a 14-day free trial.

**What you need**

* A ChatHive account. You can create one at [chathive.ca](https://chathive.ca/signup); there are 14 days free, with no card.
* The landing page you register in ChatHive must be this website's address. ChatHive only starts the chat on the site it was set up for.

**What you get**

* The chat on every page, or only on the pages where you add the "ChatHive Chat" block or the `[chathive]` shortcode.
* A "Test connection" button that tells you, in plain words, what is wrong if the chat does not start.
* The chat, its look, its name, its language, your topics, your leads and your meetings are managed in ChatHive, not in WordPress.

**Languages**: the chat speaks English, Portuguese, Spanish and French, as you choose in ChatHive.

== External services ==

This plugin connects your site to the **ChatHive** service (https://chathive.ca), which is run by ChatHive and is required for the chat to work. The plugin does nothing, and contacts nobody, until you enter a ChatHive Site ID on its settings page.

Once a Site ID is saved:

* **Every page where the chat is shown** loads the chat script from `https://api.chathive.ca/widget.js` and asks the ChatHive service to start the chat. This sends the Site ID and your site's domain. As with any web request, the visitor's browser also sends its IP address and browser details to ChatHive.
* **When a visitor uses the chat**, what they type, any contact details they give, and their browser's time zone are sent to ChatHive, which answers and, if the visitor leaves their details, saves them as a lead for you.
* **When you press "Test connection"** in the settings, your site sends the Site ID and its domain to ChatHive once, as a test.

The chat does not set cookies or store anything in the visitor's browser.

ChatHive is a paid service with a 14-day free trial. The plugin itself is free, has no locked features and does not stop working because of the trial.

* Terms of Service: https://chathive.ca/terms
* Privacy Policy: https://chathive.ca/privacy

The plugin suggests wording for your own privacy policy (Settings, then Privacy). Tell your visitors that the site uses a chatbot.

== Installation ==

1. Install and activate the plugin (Plugins, then Add New, search for "ChatHive").
2. Create your ChatHive account at https://chathive.ca/signup and set your landing page to this website's address.
3. In ChatHive, open Setup, then Widget, and copy your Site ID (it starts with `site_`).
4. In WordPress, open Settings, then ChatHive, paste the Site ID, choose where to show the chat and save.
5. Press "Test connection". When it says "Connected", open your site: the chat button is at the bottom.

Step by step, with pictures: https://chathive.ca/wordpress

== Frequently Asked Questions ==

= Do I need a ChatHive account? =

Yes. The plugin only shows the chat; the chatbot itself lives in your ChatHive account. Creating one is free for 14 days, with no card.

= The chat does not show on my site. =

Press "Test connection" in Settings, then ChatHive. It tells you if the Site ID is wrong, if the landing page in ChatHive is not this website's address, or if the account has no active subscription. Also check that you saved the Site ID and that the "Where to show the chat" option is what you expect.

= How do I show the chat on only some pages? =

Choose "Only on pages where I add the ChatHive Chat block or the [chathive] shortcode" in the settings, then add the block (search for "ChatHive") or the shortcode to those pages.

= How do I hide the chat on a page, such as the checkout? =

Add this to your theme or a small plugin: `add_filter( 'chathive_should_load', function ( $load ) { return is_page( 'checkout' ) ? false : $load; } );`

= Does the chat use cookies? =

No. The chat widget does not set cookies and does not use the browser's storage.

= Is my visitors' data safe? =

Chats are processed by ChatHive. See the ChatHive Privacy Policy linked above for what is kept and for how long.

= Which PHP version do I need? =

PHP 8.2 or newer. Older PHP versions no longer get security fixes, so ask your host to move your site to a supported version and keep it updated.

= Can I use a self-hosted ChatHive? =

Developers can set `CHATHIVE_API_URL` (and `CHATHIVE_APP_URL`) in `wp-config.php`.

== Changelog ==

= 1.0.0 =
* First release: Site ID setting, chat on every page or only where placed (block and shortcode), connection test, privacy policy text.

== Upgrade Notice ==

= 1.0.0 =
First release.
