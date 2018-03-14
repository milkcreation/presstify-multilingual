<?php

namespace tiFy\Plugins\Multilingual\Duplicate\PostType;

use tiFy\Plugins\Multilingual\Multilingual;
use tiFy\tiFy;

final class PostType extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('post_action_tiFyMultilingualDuplicatePost');
        $this->appAddAction('admin_notices');
    }

    /* = DECLENCHEURS = */
    /** == Action de duplication des pages liste == **/
    final public function post_action_tiFyMultilingualDuplicatePost($post_id)
    {
        // Bypass
        if (empty($post_id)) {
            return new \WP_Error(__('Le contenu original est indisponible', 'tify'),
                'tiFyMultilingualDuplicatePost-UnavailableInput');
        }

        if (empty($_REQUEST['lang'])) {
            return new \WP_Error(__('La langue de traduction est indisponible', 'tify'),
                'tiFyMultilingualDuplicatePost-UnavailableLang');
        }

        $blog_id = absint($_REQUEST['lang']);

        check_admin_referer('tify_multilingual_duplicate_post:' . $post_id);

        // Duplication de l'élément
        $results = $this->duplicate($post_id, $blog_id);

        if (is_wp_error($results)) {
            wp_die($results->get_error_message());
        }

        if (!$sendback = wp_get_referer()) :
            $sendback = admin_url('edit.php');
            if ($post_type = get_post_type($post_id)) :
                $sendback = add_query_arg('post_type', $post_type, $sendback);
            endif;
        endif;

        wp_redirect(add_query_arg(['message' => 'tiFyMultilingualDuplicatedPost'], $sendback));
        exit();
    }

    /** == Notification de l'interface d'adminitration == **/
    final public function admin_notices()
    {
        if (empty($_REQUEST['message']) || ($_REQUEST['message'] != 'tiFyMultilingualDuplicatedPost')) {
            return;
        }
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Le contenu a été dupliqué avec succès', 'tify'); ?></p>
        </div>
        <?php
    }

    /* = CONTROLEURS = */
    /** == Duplication de post == **/
    private function duplicate($post_id, $blog_ids = null)
    {
        // Définition du type de post
        $post_type = get_post_type($post_id);

        // Bypass
        if (!Multilingual::hasTranslatePostType($post_type)) :
            return new \WP_Error(__('Le type du contenu original n\'est pas autorisé à être dupliqué', 'tify'),
                'tiFyMultilingualDuplicatePost-InputTypeNotAllowed');
        endif;

        $post_type_attrs = Multilingual::getTranslatePostType($post_type);

        // Instanciation du contrôleur
        $className = '\tiFy\Plugins\Multilingual\Duplicate\PostType\Factory';

        $overridePath[] = "\\" . self::getOverrideNamespace() . "\\Plugins\\Multilingual\\Duplicate\\PostType\\" . self::sanitizeControllerName($post_type);

        $Cloner = self::loadOverride($className, $overridePath);
        $Cloner->setInput($post_id, $post_type_attrs['meta']);

        if ($blog_ids) :
            $post_type_attrs['blog'] = $blog_ids;
        endif;

        return $Cloner->duplicate($post_type_attrs);
    }
}