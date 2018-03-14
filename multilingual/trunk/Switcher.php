<?php
namespace tiFy\Plugins\Multilingual;

use tiFy\Core\Control\DropdownMenu\DropdownMenu;

class Switcher
{
    /* = ARGUMENTS = */
    // Instance
    private static $Instance;    
    
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        add_filter( 'the_content', array( $this, 'the_content' ) );
    }
    
    /* = DECLENCHEURS = */
    /** == Initialisation globale == **/
    final public function init()
    {
        // Régles de réécriture
        \add_rewrite_tag('%lang%', '([^&]+)');
        \add_rewrite_tag('%translate%', '([^&]+)');
        \add_rewrite_rule('^lang/([0-9]+)/([0-9]+)/?', 'index.php?lang=$matches[1]&translate=$matches[2]', 'top'); 
        \add_rewrite_rule('^lang/([0-9]+)/?', 'index.php?lang=$matches[1]', 'top');
        
        // Création des régles de réécritures
        $rewrite_rules = get_option( 'rewrite_rules' );
        if( ! isset( $rewrite_rules['^lang/([0-9]+)/([0-9]+)/?'] ) || ! isset( $rewrite_rules['^lang/([0-9]+)/?'] ) ) :
            flush_rewrite_rules();
        endif;
    }
    
    /** == == **/
    final public function template_redirect()
    {         
        if( ! $blog_id = (int) get_query_var( 'lang' ) )
           return;

        $location = get_site_url( $blog_id );
           
        if( ! $post_id = get_query_var( 'translate' ) ) :
            wp_redirect( $location );
            exit;
        endif;
        
        if( $blog_id === get_current_blog_id() ) :
            $location = get_permalink( $post_id );
        elseif( $translate_id = get_post_meta( $post_id, '_translate_blog_'.$blog_id, true ) ) :
            switch_to_blog( $blog_id );
                $location = get_permalink( $translate_id ); 
            restore_current_blog();
        elseif( $dmz = get_blog_option( $blog_id, 'tify_multilingual_dmz' ) ) :
            $location = add_query_arg( array( '_lang' => get_current_blog_id(), '_translate' => $post_id ), get_blog_permalink( $blog_id, $dmz ) );
        endif;   
        
        wp_redirect( $location );
        exit;
    }
    
    /** == BOUTON DE REDIRECTION VERS LE CONTENU D'ORIGINE == **/
    final public function the_content( $content )
    {
        if( get_the_ID() != get_option( 'tify_multilingual_dmz' ) )
            return $content;
        if( get_option( 'tify_multilingual_backto_origin' ) != 'on' )
            return $content;
        if( empty( $_REQUEST['_lang'] ) && empty( $_REQUEST['_translate'] ) )
            return $content;
        
        $content .= "<div class=\"tiFyMultilingual-backToOrigin\">\n";
        $content .= "\t<a href=\"".esc_url( get_blog_permalink( $_REQUEST['_lang'], $_REQUEST['_translate'] ) )."\" class=\"tiFyMultilingual-backToOriginLink\">\n";
        $content .= __( "Revenir au contenu d'origine", 'tify' );
        $content .= "\t</a>\n";
        $content .= "</div>";
        return $content;
    }
    
    /* = CONTROLEURS = */    
    /** == Selecteur de langage == **/
    public static function display( $args = array( ), $echo = true )
    {
        self::$Instance++; 
        
        $defaults = array(
            'id'            => 'tiFyMultilingual_Switcher--'. self::$Instance,
            'class'         => '',
            'selected'      => get_current_blog_id(),
            'display'       => 'dropdown',  // dropdown (default) | inline | list
            'separator'     => '&nbsp;|&nbsp;',
            'label'         => 'iso',       // iso -ex : fr | language - ex: fr_FR | english_name - ex : French (France) | native_name - ex : Français
            'labels'        => array( ),    // Intitulés personnalisés, tableaux indexés par blog_id
            'flag'          => false
        );
        $args = wp_parse_args( $args, $defaults );

        // Création des liens
        $args['links'] = array();
        foreach( Multilingual::getSites() as $site ) :    
            if( $site->archived || $site->deleted )
                continue;
            
            $blog_id = $site->blog_id;
            $locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
            if( ! empty( $args['labels'][$blog_id] ) ) :
                $label = $args['labels'][$blog_id];
            elseif( $translation = Multilingual::getTranslation( $locale ) ) :
                $label = ( $args['label'] === 'iso' ) ?  $translation['iso'][1] : $translation[ $args['label'] ];
            else :
                $label = $locale;
            endif;
            
            $path = '/lang/'. $blog_id;
            if( is_singular() )
                $path .= '/'. get_the_ID();  
   
            $args['links'][$blog_id]  = "";
            $args['links'][$blog_id] .= "<a href=\"". get_site_url( null, $path ) ."\" class=\"tiFyMultilingual_SwitcherItemLink". ( $args['selected'] == $blog_id ? ' selected' : '' )."\">";
            if( $args['flag'] )
                $args['links'][$blog_id] .= Multilingual::getFlag( $blog_id );
            $args['links'][$blog_id] .= $label ."</a>";
        endforeach;
        
        $output = "";
        
        if( $args['display'] == 'dropdown' ) :
            $_args = $args;
            $_args['picker'] = [
                'class'         => 'tiFyMultilingual_Switcher-picker'
            ];
            
            $output .= DropdownMenu::display($_args, false);
            
        elseif( $args['display'] == 'inline' ) :
        
            $output .= "<div id=\"{$args['id']}\" class=\"tiFyMultilingual_Switcher tiFyMultilingual_Switcher--inline {$args['class']}\">". implode( $args['separator'], $args['links'] ) ."</div>";
       
        elseif( $args['display'] == 'list' ) :
            
            $output .= "<div id=\"{$args['id']}\" class=\"tiFyMultilingual_Switcher tiFyMultilingual_Switcher--list {$args['class']}\">\n";
            $output .= "\t<ul class=\"tiFyMultilingual_SwitcherItems\">\n";
            foreach( $args['links'] as $link ) :
                $output .= "\t\t<li class=\"tiFyMultilingual_SwitcherItem\">". $link ."</li>\n";
            endforeach;
            $output .= "\t</ul>\n";
            $output .= "</div>\n";
            
        endif;
        
        if( $echo )
            echo $output;
        
        return $output;
    }
}