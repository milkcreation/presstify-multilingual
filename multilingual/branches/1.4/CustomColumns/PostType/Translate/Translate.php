<?php
namespace tiFy\Plugins\Multilingual\CustomColumns\PostType\Translate;

use tiFy\Plugins\Multilingual\Multilingual;

class Translate extends \tiFy\Components\CustomColumns\Factory
{
    /* = DECLENCHEURS = */
    /** == Initialisation de l'interface d'administration == **/
    public function admin_init()
    {
        add_action( 'wp_ajax_tiFyMultilingualPostColumn', array( $this, 'wp_ajax' ) );
    }
    
    /** == Mise en file des scripts de l'interface d'administration == **/
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style( 'tiFyMultilingualCustomColumnsPostTypeTranslate', self::tFyAppUrl( get_class() ). '/Translate.css', array(), 170310 );
        wp_enqueue_script( 'tiFyMultilingualCustomColumnsPostTypeTranslate', self::tFyAppUrl( get_class() ). '/Translate.js', array( 'jquery' ), 170310, true );
    }
    
    /* = CONTROLEURS = */
    /** == Récupération des arguments par défaut == **/
    public function getDefaults()
    {
        return array(
            'title'         =>     __( 'Traductions', 'tify' ),
            'position'      => 2
        );    
    }
            
    /** == Affichage des données de la colonne == **/
    public function content( $column, $post_id )
    {
        $output = ""; 
        foreach( Multilingual::getSites() as $site ) :
            if( $site->blog_id == get_current_blog_id() )
                continue;
            if( ! $post = get_post( $post_id ) )
                continue;
            
            $post_type_object = get_post_type_object( $post->post_type );
            $translate_id = ( $translate_id = get_post_meta( $post_id, '_translate_blog_'. $site->blog_id, true ) ) ? $translate_id : 0; 
            $href = ! $translate_id ? wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'lang' => $site->blog_id, 'action' => 'tiFyMultilingualDuplicatePost' ), admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) ) ), 'tify_multilingual_duplicate_post:'. $post->ID ) : '';
            
            $output .= "\t<li class=\"tiFyMultilingual_LocaleItem". ( $translate_id ? ' tiFyMultilingual_LocaleItem--translated' : '') ."\">\n";
            $output .= "\t\t<a href=\"{$href}\" class=\"tiFyMultilingual_LocaleItemLink\" data-blog_id=\"{$site->blog_id}\" data-post_id=\"{$translate_id}\">\n";
            $output .= Multilingual::getFlag( $site->blog_id, array( 'width' => '25', 'height' => 'auto' ) );
            $output .= "\t\t\t<strong>". ( $translate_id ? __( 'Voir le détail', 'tify' ): __( 'Créer une copie', 'tify' ) ) ."</strong>";
            $output .= "\t\t</a>\n";
            $output .= "\t\t<img class=\"tiFyMultilingual_LocaleItemSpinner\" src=\"". admin_url( '/images/spinner.gif' ) ."\" width=\"16px\" height=\"auto\"/>";
            $output .= "\t\t<div class=\"tiFyMultilingual_LocaleItemRowActions\"></div>\n";
            $output .= "\t</li>\n";
        endforeach;

        if( $output ) :
            $output = "<ul class=\"tiFyMultilingual_LocaleItems\">\n{$output}</ul>\n";
        endif;
        
        echo $output;
    }
    
    /** == == **/
    public function wp_ajax()
    {
        $blog_id = absint( $_POST['blog_id'] );
        $post_id = absint( $_POST['post_id'] );        
        
        switch_to_blog( $blog_id );
            if( ! $post = get_post( $post_id ) )
                wp_send_json_error( array( 'error' => __( 'Contenu indisponible', 'tify' ) ) );
        
            $post_type_object = get_post_type_object( $post->post_type );
    		$can_edit_post = current_user_can( 'edit_post', $post->ID );
    		$actions = array();
    		$title = _draft_or_post_title( $post->ID );           
            $edit_post_link = get_edit_post_link( $post->ID, '' );
            
            // Edition
            if ( $can_edit_post && 'trash' != $post->post_status ) :
                $actions['edit'] =  sprintf(
				                        '<a href="%s" aria-label="%s" target=\"_blank\">%s</a>',
				                        get_edit_post_link( $post->ID ),
				                        esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
				                        __( 'Edit' )
			                        );
            endif;
           
            // Aperçu
            if( is_post_type_viewable( $post_type_object ) ) :
    			if( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) :
    				if( $can_edit_post ) :
    					$preview_link = get_preview_post_link( $post );
    					$actions['view'] = sprintf(
                        						'<a href="%s" rel="permalink" aria-label="%s" target=\"_blank\">%s</a>',
                        						esc_url( $preview_link ),
                        						/* translators: %s: post title */
                        						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ),
                        						__( 'Preview' )
                        					);
    				endif;
    			elseif( 'trash' != $post->post_status ) :
    				$actions['view'] = sprintf(
                        					'<a href="%s" rel="permalink" aria-label="%s" target=\"_blank\">%s</a>',
                        					get_permalink( $post->ID ),
                        					/* translators: %s: post title */
                        					esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
                        					__( 'View' )
                        				);
    			endif;
            endif;        
        restore_current_blog();
        
        // Formatage des l'affichage des actions
        foreach( $actions as $action => &$link ) :
            $link = "<span class=\"{$action}\">{$link}</span>";
        endforeach;        
        $actions = implode( ' | ', $actions );
                
        wp_send_json_success( compact( 'title', 'actions', 'edit_post_link' ) );
    }    
}