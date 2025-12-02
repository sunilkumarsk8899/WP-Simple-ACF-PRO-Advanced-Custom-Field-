Advanced Custom Fields Pro (Clone)

A lightweight, developer-friendly custom fields plugin for WordPress that replicates core features of ACF Pro. It includes powerful field types like Repeaters, Galleries, and Groups, along with a native "Options Page" feature.

🚀 Features

Repeater Fields: Create complex, repeatable lists of data (e.g., Team Members, Slides).

Gallery Field: Drag-and-drop multiple images with reordering support.

Group Field: Nest fields within a parent group for better organization.

Color Picker: Native WordPress color picker.

Advanced Location Rules: Assign fields to specific Post Types, specific Pages (by ID), Page Templates, or the global Options Page.

Options Page: A dedicated admin page for global site settings (Footer text, Social links, etc.).

Developer API: Simple helper function get_sas_field() to retrieve data anywhere.

📦 Installation

Download the repository as a ZIP file.

Log in to your WordPress Admin Dashboard.

Navigate to Plugins > Add New > Upload Plugin.

Upload the ZIP file and click Install Now.

Activate the plugin.

🛠️ Usage Guide

1. Creating a Field Group

Go to Custom Fields in the admin sidebar.

Click Add New.

Give your group a title (e.g., "Homepage Settings").

2. Adding Fields

Click the + Add Field button to add a new field. You can configure:

Label: The name shown to the user.

Name (Key): The slug used in code (e.g., hero_image).

Type: Choose from Text, Textarea, Image, Gallery, Color, Repeater, or Group.

For Repeater / Group Fields:

Select "Repeater" or "Group" as the type.

A "Sub Fields" builder will appear.

Click + Add Sub Field to add fields inside that repeater (e.g., Title, Image, Description).

3. Assigning Locations

Use the Location Rules box on the right (or bottom) to decide where these fields appear.

Post Type: Show on all Posts, Pages, or Custom Post Types.

Page (Specific): Show only on a specific page (e.g., "Home").

Page Template: Show on any page using a specific template.

Options Page: Show on the global "Site Options" page.

💻 Developer Guide (Displaying Fields)

Use the helper function get_sas_field('field_key') in your theme files (single.php, page.php, header.php, etc.).

Basic Fields (Text, Color)

$title = get_sas_field('hero_title');
if( $title ) {
    echo '<h1>' . esc_html($title) . '</h1>';
}


Image Field

Returns the Attachment ID.

$image_id = get_sas_field('hero_image');
if( $image_id ) {
    echo wp_get_attachment_image( $image_id, 'full' );
}


Gallery Field

Returns an array of Attachment IDs.

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


Repeater Field

Loop through rows of data. Example with Title, Image, and Background Color.

$slides = get_sas_field('home_slider'); 

if( $slides ) {
    echo '<div class="slider">';
    foreach( $slides as $slide ) {
        // Get sub-field values safely
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


Group Field

Access sub-fields directly from the group array.

$hero = get_sas_field('hero_section');
if( $hero ) {
    echo '<h1>' . esc_html($hero['main_heading']) . '</h1>';
    echo '<p>' . esc_html($hero['sub_text']) . '</p>';
}


Options Page (Global Data)

// Retrieve data saved in "Site Options"
$footer_copyright = get_sas_field('footer_copyright_text');
echo $footer_copyright;


📄 License

GPLv2 or later.Advanced Custom Fields Pro (Clone)

A lightweight, developer-friendly custom fields plugin for WordPress that replicates core features of ACF Pro. It includes powerful field types like Repeaters, Galleries, and Groups, along with a native "Options Page" feature.

🚀 Features

Repeater Fields: Create complex, repeatable lists of data (e.g., Team Members, Slides).

Gallery Field: Drag-and-drop multiple images with reordering support.

Group Field: Nest fields within a parent group for better organization.

Color Picker: Native WordPress color picker.

Advanced Location Rules: Assign fields to specific Post Types, specific Pages (by ID), Page Templates, or the global Options Page.

Options Page: A dedicated admin page for global site settings (Footer text, Social links, etc.).

Developer API: Simple helper function get_sas_field() to retrieve data anywhere.

📦 Installation

Download the repository as a ZIP file.

Log in to your WordPress Admin Dashboard.

Navigate to Plugins > Add New > Upload Plugin.

Upload the ZIP file and click Install Now.

Activate the plugin.

🛠️ Usage Guide

1. Creating a Field Group

Go to Custom Fields in the admin sidebar.

Click Add New.

Give your group a title (e.g., "Homepage Settings").

2. Adding Fields

Click the + Add Field button to add a new field. You can configure:

Label: The name shown to the user.

Name (Key): The slug used in code (e.g., hero_image).

Type: Choose from Text, Textarea, Image, Gallery, Color, Repeater, or Group.

For Repeater / Group Fields:

Select "Repeater" or "Group" as the type.

A "Sub Fields" builder will appear.

Click + Add Sub Field to add fields inside that repeater (e.g., Title, Image, Description).

3. Assigning Locations

Use the Location Rules box on the right (or bottom) to decide where these fields appear.

Post Type: Show on all Posts, Pages, or Custom Post Types.

Page (Specific): Show only on a specific page (e.g., "Home").

Page Template: Show on any page using a specific template.

Options Page: Show on the global "Site Options" page.

💻 Developer Guide (Displaying Fields)

Use the helper function get_sas_field('field_key') in your theme files (single.php, page.php, header.php, etc.).

Basic Fields (Text, Color)

$title = get_sas_field('hero_title');
if( $title ) {
    echo '<h1>' . esc_html($title) . '</h1>';
}


Image Field

Returns the Attachment ID.

$image_id = get_sas_field('hero_image');
if( $image_id ) {
    echo wp_get_attachment_image( $image_id, 'full' );
}


Gallery Field

Returns an array of Attachment IDs.

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


Repeater Field

Loop through rows of data. Example with Title, Image, and Background Color.

$slides = get_sas_field('home_slider'); 

if( $slides ) {
    echo '<div class="slider">';
    foreach( $slides as $slide ) {
        // Get sub-field values safely
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


Group Field

Access sub-fields directly from the group array.

$hero = get_sas_field('hero_section');
if( $hero ) {
    echo '<h1>' . esc_html($hero['main_heading']) . '</h1>';
    echo '<p>' . esc_html($hero['sub_text']) . '</p>';
}


Options Page (Global Data)

// Retrieve data saved in "Site Options"
$footer_copyright = get_sas_field('footer_copyright_text');
echo $footer_copyright;


📄 License

GPLv2 or later.
