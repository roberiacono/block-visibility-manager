=== Block Visibility Manager ===
Contributors:      prototipo88
Tags:              visibility, block visibility, block, block editor, gutenberg
Tested up to:      7.0
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Control the visibility of Gutenberg blocks based on user role, device type, date, time, and more. Enhance content flexibility by dynamically showing or hiding blocks under specific conditions.


== Description ==

**Block Visibility Manager** gives you fine-grained control over which blocks support visibility rules in the block editor. 

You can conditionally show or hide individual block based on:

- Time
- Date and time
- User roles (including guests)
- Device type (mobile, tablet, desktop)


=== Benefits ===

- Show a block only during a limited time (e.g. event banners)
- Hide a block for guests
- Improve content relevance and scheduling without complex tools.
- Keep the editor clean by enabling visibility only where needed.
- No performance overhead or custom block lock-in.
- 100% native: works seamlessly with WordPress Core and Gutenberg.


== Features ==

- **Time Scheduling** – Show or hide blocks between specific times, everyday (based on server time).
- **Date & Time Scheduling** – Show or hide blocks between specific dates with time for granular control, based on UTC+0 timezone. For example you can display a block only on Amazon Prime Days, from 00:00 of the first day to 23:59 of the last day.
- **User Role Targeting** – Restrict visibility based on WP user roles, such as Administrator, Editor, Subscriber, and others — or hide/show blocks specifically for guests (non-logged-in users).
- **Device-Based Display** – Conditionally hide blocks on mobile, tablet, or desktop. his is achieved using fixed CSS media queries, ensuring consistent behavior across themes without relying on JavaScript.
- **Selective Block Control** – Choose which blocks support visibility settings.
- **Fully Native** – Built using core WordPress components and filters only.


== Who Is It For? ==

- **Content creators** who want to show time-sensitive content.
- **Developers and site builders** who prefer native, non-bloated solutions.
- **Agencies** looking to offer clients role-based or scheduled content blocks.
- **Marketers** who need to hide/show promotions or messages dynamically.


== Why Use This Plugin? ==

Need to:

- Show a block only during a limited time (e.g. event banners)?
- Hide a message from logged-out users?
- Disable visibility options on blocks that don’t need them.
- Avoid third-party plugins that add extra blocks or scripts?

**Block Visibility Manager solves these problems cleanly and natively.**


== Technical Notes ==

**No Blocks Added**
This plugin does not add any new blocks. Instead, it **extends existing core WordPress blocks** using official filters and native UI components, keeping your site fast, lean, and fully compatible with WordPress standards.

**Device-Based Visibility**
The hiding functionality for mobile, tablet, or desktop relies purely on CSS using `display: none;`. No JavaScript is involved. This ensures lightweight and consistent behavior across different themes.

**Server-Side Visibility Filtering**
Other visibility conditions (such as time range, date range, or user roles) are enforced server-side. If a block does not meet the defined conditions, it is entirely removed from the rendered HTML output.

**Editor Behavior**
Visibility settings currently apply only on the frontend. Blocks remain fully visible in the editor to avoid confusion during content creation. Visibility conditions are not previewed in the block editor at this time.

**Block Visibility for WordPress Block Editor**
This plugin is designed exclusively for the WordPress Block Editor (Gutenberg). It does not support other page builders such as Elementor, Beaver Builder, WPBakery, or others.

If you are using a block-based theme or editing content with the native WordPress block editor, this plugin will seamlessly integrate. For other builders, visibility settings will have no effect.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/block-visibility-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings > Block Visibility** to choose which blocks should support visibility options.



== Frequently Asked Questions ==

= Does this plugin add new blocks? =  
No, it extends **existing WordPress core blocks** via filters.

= Will it work with custom blocks? =  
Only core blocks are supported by default. Third-party blocks may work if registered using `block.json`.

= Can I remove the plugin without affecting saved content? =  
Yes. Visibility settings are saved in block attributes and will not break your layout.

= Does it load custom scripts? =  
No. It uses **WordPress core components** and no custom frontend JavaScript.


== Screenshots ==

1. Block Visibility Manager in action.
2. Visibility options available inside block settings.
3. Admin panel to enable or disable visibility support per block.
4. Example: Set a block to show only during a specific date/time range.
5. Example: Set a block to show everyday only during a specific time range, and hide it on mobile.
6. Example: Hide banner on mobile.

== Changelog ==

= 1.0.0 =
* Release
