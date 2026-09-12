<?php

namespace FitsPropertiesCore\Content;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates simple solid-color placeholder photos for demo content so
 * seeded properties/agents don't look broken with no featured image.
 * Real photos should replace these before the site goes live.
 */
class PlaceholderImage
{
    private static $palette = [
        [23, 51, 82], [30, 66, 99], [61, 90, 128], [94, 74, 46], [141, 111, 63], [45, 74, 62],
    ];

    public static function create($label, $width = 1200, $height = 800)
    {
        if (!function_exists('imagecreatetruecolor')) {
            return 0;
        }

        $color = self::$palette[array_rand(self::$palette)];
        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        imagefill($image, 0, 0, $bg);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        $font = 5;
        $text = $label;
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = (int) (($width - $textWidth) / 2);
        $y = (int) ($height / 2);
        imagestring($image, $font, max($x, 10), $y, $text, $textColor);

        $uploadDir = wp_upload_dir();
        $filename = 'fp-demo-' . sanitize_title($label) . '-' . wp_generate_password(6, false) . '.jpg';
        $filePath = trailingslashit($uploadDir['path']) . $filename;

        imagejpeg($image, $filePath, 82);
        imagedestroy($image);

        $fileType = wp_check_filetype($filename, null);
        $attachment = [
            'post_mime_type' => $fileType['type'],
            'post_title' => $label,
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachmentId = wp_insert_attachment($attachment, $filePath);

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $metadata = wp_generate_attachment_metadata($attachmentId, $filePath);
        wp_update_attachment_metadata($attachmentId, $metadata);

        return $attachmentId;
    }
}
