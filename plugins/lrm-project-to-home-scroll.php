<?php
/**
 * Plugin Name: LRM Project Bottom Redirect
 * Description: Redirects visitors from project pages back to the homepage when they reach the bottom of the page.
 * Version: 1.0.0
 * Author: LRM
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Print redirect script only on singular project-like pages, excluding the front page.
 *
 * Kept as a MU plugin so theme updates do not overwrite the behavior.
 */
function lrm_project_bottom_redirect_script() {
    if (is_front_page()) {
        return;
    }

    if (!is_singular()) {
        return;
    }

    $home_url = home_url('/');
    ?>
    <script>
        (function () {
            var redirectUrl = <?php echo wp_json_encode($home_url); ?>;
            var thresholdPx = 24;
            var hasRedirected = false;

            function isLikelyProjectPage() {
                var body = document.body;
                if (!body) {
                    return false;
                }

                var classes = body.className || '';

                return (
                    classes.indexOf('single-project') !== -1 ||
                    classes.indexOf('post-type-project') !== -1 ||
                    classes.indexOf('type-project') !== -1 ||
                    classes.indexOf('lay-project') !== -1 ||
                    classes.indexOf('single-portfolio') !== -1
                );
            }

            function atBottomOfPage() {
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
                var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
                var documentHeight = Math.max(
                    document.body.scrollHeight,
                    document.documentElement.scrollHeight,
                    document.body.offsetHeight,
                    document.documentElement.offsetHeight,
                    document.body.clientHeight,
                    document.documentElement.clientHeight
                );

                return (scrollTop + viewportHeight) >= (documentHeight - thresholdPx);
            }

            function maybeRedirect() {
                if (hasRedirected) {
                    return;
                }

                if (!isLikelyProjectPage()) {
                    return;
                }

                if (!atBottomOfPage()) {
                    return;
                }

                hasRedirected = true;

                document.documentElement.classList.add('lrm-transitioning-home');
                document.body.classList.add('lrm-transitioning-home');

                window.setTimeout(function () {
                    window.location.assign(redirectUrl);
                }, 300);
            }

            window.addEventListener('scroll', maybeRedirect, { passive: true });
            window.addEventListener('resize', maybeRedirect);
            document.addEventListener('DOMContentLoaded', maybeRedirect);
            window.setTimeout(maybeRedirect, 200);
        })();
    </script>
    <style>
        .lrm-transitioning-home body,
        body.lrm-transitioning-home {
            transition: opacity 280ms ease;
            opacity: 0;
        }
    </style>
    <?php
}
add_action('wp_footer', 'lrm_project_bottom_redirect_script', 99);
