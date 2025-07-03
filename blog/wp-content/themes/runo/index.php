<?php get_header(); ?>

<style>
    .blogs .card-body {
        margin-top: 5px !important;
    }

    .blog-card {

        height: 100%;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
    }

    .blog-card .card-head {

        max-width: 100%;
    }

    .blog-card .card-head img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .blog-card .card-body {
        flex: 1 1 60%;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .blog-card .card-title {
        font-size: 1.25rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
</style>



<div class="blogs">
    <div class="container py-5">
        <h1 class="mb-20 text-center">Latest Blog Posts</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4"><?php
                                                        // Custom query for 1 post per page
                                                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

                                                        $args = [
                                                            'posts_per_page' => 15,
                                                            'paged' => $paged,
                                                        ];

                                                        $custom_query = new WP_Query($args);

                                                        if ($custom_query->have_posts()) :
                                                            while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
                    <div class="col">
                        <div class="card blog-card h-100">
                            <div class="card-head">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>">
                                <?php else : ?>
                                    <img src="https://via.placeholder.com/400x200?text=No+Image" alt="No image">
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php the_title(); ?></h5>
                                <p class="card-text blog-desc"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                <a href="<?php the_permalink(); ?>" class="btn-runo mt-auto text-center">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php
                                                            endwhile;
                                                        else : ?><p>No posts found.</p><?php endif; ?>
        </div>
        <!-- Pagination -->
        <div class="mt-5"><?php
                            $pagination = paginate_links([
                                'total' => $custom_query->max_num_pages,
                                'current' => $paged,
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'type' => 'array',
                            ]);

                            if (!empty($pagination)) :
                            ?><nav>
                    <ul class="pagination justify-content-center"><?php foreach ($pagination as $page) : ?><li class="page-item <?php if (strpos($page, 'current') !== false) echo 'active'; ?>"><?php echo str_replace('page-numbers', 'page-link', $page); ?></li><?php endforeach; ?></ul>
                </nav><?php endif; ?></div>
    </div>
</div>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>