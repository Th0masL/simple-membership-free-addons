<?php

function swpm_fpp_lockdown_legacy_template($content = ''){
    get_header();
    echo $content;
    get_footer();
}

function swpm_fpp_lockdown_fse_template($content = '') {
    ob_start();
    ?>
    <div class="swpm_fpp_lockdown_fse_header">
        <?php swpm_fpp_lockdown_fse_header(); ?>
    </div>

    <!-- wp:group {"tagName":"main","layout":{"type":"default"}} -->
    <main class="wp-block-group">
        <!-- wp:group {"style":{"spacing":{"padding":{"top":"0vh","bottom":"6vh"}}},"layout":{"type":"constrained"}} -->
        <div class="wp-block-group">
            <div class="swpm_fpp_lockdown_fse_page_content">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
        <!-- /wp:group -->
    </main>
    <!-- /wp:group -->

    <div class="swpm_fpp_lockdown_fse_footer">
        <?php swpm_fpp_lockdown_fse_footer(); ?>
    </div>

    <?php
    $output = ob_get_clean();
    $output = apply_filters('swpm_fpp_lockdown_fse_template', $output, $content);
    
    $blocks = apply_filters('swpm_fpp_lockdown_fse_template_blocks', parse_blocks($output));
    ?>

    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?> class="swpm_fpp_lockdown_fse_page_body">
        <?php wp_body_open(); ?>
        <?php
        foreach ($blocks as $block) {
            echo render_block($block);
        }
        ?>
        <?php wp_footer(); ?>
    </body>
    </html>
<?php
}

function swpm_fpp_lockdown_fse_header() {
    $path = get_stylesheet_directory() . '/parts/header.html';
    if (!file_exists($path)) {
        $path = get_template_directory() . '/parts/header.html';
    }

    if (!file_exists($path)) {
        return;
    }

    $content = file_get_contents($path);
    
    echo apply_filters('swpm_fpp_lockdown_fse_header', $content);
}

function swpm_fpp_lockdown_fse_footer() {
    $path = get_stylesheet_directory() . '/parts/footer.html';
    if (!file_exists($path)) {
        $path = get_template_directory() . '/parts/footer.html';
    }
    if (!file_exists($path)) {
        return;
    }
    
    $content = file_get_contents($path);
    
    echo apply_filters('swpm_fpp_lockdown_fse_footer', $content);
}