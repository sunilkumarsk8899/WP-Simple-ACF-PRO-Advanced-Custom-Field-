Advanced Custom Fields Pro (Clone)

A lightweight, developer-friendly custom fields plugin for WordPress that replicates core features of ACF Pro. Includes powerful field types like Repeaters, Galleries, and Groups, along with a native "Options Page" feature.

🚀 Features

This plugin brings the premium power of custom fields to your WordPress site without the overhead.

🔁 Repeater Fields: Create complex, repeatable lists of data (e.g., Team Members, Slides, Testimonials).

🖼️ Gallery Field: Drag-and-drop multiple images with full reordering support.

📂 Group Field: Nest fields within a parent group for better organization and cleaner data structures.

🎨 Color Picker: Native WordPress color picker integration.

📍 Advanced Location Rules: Assign fields with precision:

Specific Post Types (Posts, Pages, Custom Post Types)

Specific Pages (by ID)

Page Templates

Global Options Page

⚙️ Options Page: A dedicated admin page for global site settings (Footer text, Social links, API keys).

💻 Developer API: Simple, robust helper function get_sas_field() to retrieve data anywhere in your theme.

📦 Installation

Download: Clone this repository or download the ZIP file.

Upload: Log in to your WordPress Admin Dashboard and navigate to Plugins > Add New > Upload Plugin.

Install: Upload the ZIP file and click Install Now.

Activate: Activate the plugin from the Plugins menu.

🛠️ Usage Guide

1. Creating a Field Group

Navigate to Custom Fields in the admin sidebar.

Click Add New.

Give your group a descriptive title (e.g., "Homepage Hero Section").

2. Adding Fields

Click the + Add Field button. Configure the following:

Label: The human-readable name shown to the user.

Name (Key): The slug used in your code (e.g., hero_image).

Type: Select from Text, Textarea, Image, Gallery, Color, Repeater, or Group.

Working with Repeater / Group Fields

Select "Repeater" or "Group" as the field type.

A "Sub Fields" builder will appear below.

Click + Add Sub Field to add fields inside that repeater (e.g., Title, Image, Description).

Drag and drop to reorder sub-fields.

3. Assigning Locations

Use the Location Rules box to determine where these fields appear. You can mix and match rules using OR logic.

Post Type: Show on all Posts, Pages, or CPTs.

Page (Specific): Show only on a specific page ID.

Page Template: Show on any page using a specific template file.

Options Page: Show on the global "Site Options" page.

💻 Developer Guide

Retrieve any field value using the simple helper function:

// $value = get_sas_field('field_name');


Examples

Basic Fields (Text, Color)

/*
$title = get_sas_field('hero_title');
$bg_color = get_sas_field('hero_bg_color');

if( $title ) {
    echo '<h1 style="color: ' . esc_attr($bg_color) . ';">' . esc_html($title) . '</h1>';
}
*/


Image Field

Returns the Attachment ID. Use WordPress functions to display it.

/*
$image_id = get_sas_field('hero_image');
if( $image_id ) {
    echo wp_get_attachment_image( $image_id, 'full' );
}
*/


Gallery Field

Returns an array of Attachment IDs.

/*
$gallery_ids = get_sas_field('product_gallery');

if( $gallery_ids ) {
    echo '<div class="gallery-grid">';
    foreach( $gallery_ids as $id ) {
        echo '<div class="gallery-item">';
        echo wp_get_attachment_image( $id, 'medium' );
        echo '</div>';
    }
    echo '</div>';
}
*/


Repeater Field

Loop through rows of data. Example: A slider with Title, Image, and Background Color.

/*
$slides = get_sas_field('home_slider'); 

if( $slides ) {
    echo '<div class="slider">';
    foreach( $slides as $slide ) {
        // Safe access to sub-fields
        $title = isset($slide['title']) ? $slide['title'] : '';
        $bg_color = isset($slide['bg_color']) ? $slide['bg_color'] : '#fff';
        $img_id = isset($slide['image']) ? $slide['image'] : '';

        echo '<div class="slide" style="background-color: '.esc_attr($bg_color).'">';
            if($img_id) {
                echo wp_get_attachment_image( $img_id, 'large' );
            }
            echo '<h2>' . esc_html($title) . '</h2>';
        echo '</div>';
    }
    echo '</div>';
}
*/


Group Field

Access sub-fields directly from the group array.

/*
$hero = get_sas_field('hero_section');

if( $hero ) {
    echo '<div class="hero">';
    echo '<h1>' . esc_html($hero['main_heading']) . '</h1>';
    echo '<p>' . esc_html($hero['sub_text']) . '</p>';
    echo '</div>';
}
*/


Options Page (Global Data)

/*
// Retrieve data saved in "Site Options"
$footer_text = get_sas_field('footer_copyright_text');
echo '<div class="footer">' . esc_html($footer_text) . '</div>';
*/


📄 License

This project is licensed under the GPLv2 or later.
