<?php get_header(); ?>

<style>
    .blog-content #ez-toc-container {

        display: none !important;
    }
</style>


<div class="progress-container">
    <div class="progress-bar" id="progressBar"></div>
</div>

<script>
    jQuery(document).ready(function($) {
        var progressBar = $('#progressBar');
        var progressContainer = $('.progress-container');

        $(window).on('scroll', function() {
            var scrollTop = $(document).scrollTop();
            var scrollHeight = $(document).height() - $(window).height();
            var scrolled = (scrollTop / scrollHeight) * 100;
            progressBar.css('width', scrolled + '%');

            // Apply scroll behavior only on desktop
            if (window.innerWidth >= 768) {
                if (scrollTop > 0) {
                    progressContainer.addClass('scrolled');
                } else {
                    progressContainer.removeClass('scrolled');
                }
            }
        });
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>



<div class="container-fluid py-5">
    <div class="row">
        <!-- TOC Column - Hidden on small devices -->
        <div class="col-md-4 d-none d-md-block" style="margin-top: 30px;">
            <div class="sticky-top" style="top: 120px;">
                <?php echo do_shortcode('[toc]'); ?>

                <!-- Minimal Sticky Promo Section -->
                <div class="mt-4 p-3 rounded text-center" style="background: #fff0f0; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h6 class="fw-bold mb-1" style="color: #212121;color: #321b3a;font-size: 20px;padding: 10px 0px;">Boost Your Business Calls</h6>
                    <p class="text-muted mb-3 small">Start your free trial of RUNO’s SIM-based call management today!</p>


                    <a href="#" class="header-btn">Request Demo</a>

                </div>
            </div>
        </div>



        <!-- Blog Content Column -->
        <div class="col-md-8 text-center">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
            ?>
                    <h1 class="mb-4"><?php the_title(); ?></h1>

                    <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title_attribute(); ?>" class="img-fluid mb-4 blog-feat">
                    <?php endif; ?>

                    <div class="post-content text-left">
                        <div class="blog-content">
                            <?php the_content(); ?>
                        </div>
                    </div>

                <?php
                endwhile;
            else :
                ?>
                <p>No content found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>




<?php get_footer(); ?>