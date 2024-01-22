<?php
/*
Plugin Name: Search Autocomplete Plugin
Description: Adiciona um campo de busca com autopreenchimento usando jQuery UI.
Version: 1.0
Author: Seu Nome
*/

// Função para incluir scripts e estilos
function enqueue_autocomplete_scripts() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-autocomplete');
}
add_action('wp_enqueue_scripts', 'enqueue_autocomplete_scripts');

// Função para processar a solicitação Ajax
function autocomplete_search() {
    $term = sanitize_text_field($_GET['term']);

    $args = array(
        's' => $term,
        'post_type' => 'any',
        'posts_per_page' => 5,
    );

    $query = new WP_Query($args);

    $suggestions = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $suggestions[] = array(
                'label' => get_the_title(),
                'value' => get_permalink(),
            );
        }
    }

    wp_reset_postdata();

    echo json_encode($suggestions);
    die();
}

add_action('wp_ajax_autocomplete_search', 'autocomplete_search');
add_action('wp_ajax_nopriv_autocomplete_search', 'autocomplete_search');

// Função para gerar o shortcode
function search_autocomplete_shortcode() {
    ob_start(); ?>

    <form role="search" method="get" id="searchform" action="<?php echo home_url('/'); ?>">
        <div>
            <input type="text" value="" name="s" id="s" placeholder="Pesquisar" />
            <div id="search-autocomplete"></div>
            <input type="submit" id="searchsubmit" value="Buscar" />
        </div>
    </form>

    <script>
        jQuery(document).ready(function($) {
            $('#s').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        data: {
                            action: 'autocomplete_search',
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1, // Mínimo de 1 caractere para acionar a pesquisa
            });
        });
    </script>

    <?php
    return ob_get_clean();
}

// Registrar o shortcode
add_shortcode('search_autocomplete', 'search_autocomplete_shortcode');
