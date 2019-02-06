<?php
namespace tiFy\Plugins\Multilingual\Taboox\PostType\Translate\Admin;

use tiFy\Plugins\Multilingual\Multilingual;

class Translate extends \tiFy\Core\Taboox\Admin
{
    /* = ARGUMENTS = */
    //
    private static $Sites   = array();
    
    /* = DECLENCHEURS = */                
    /** == Initialisation globale == **/
    public function init()
    {
        add_action( 'wp_ajax_tiFyMultilingualPostTranslate', array( $this, 'wp_ajax' ) );       
    }            
        
    /** == Chargement de la page courante == **/
    public function current_screen( $current_screen )
    {
        self::$Sites = Multilingual::getSites( array( 'exclude' => get_current_blog_id() ) );        
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
    }
    
    /** == Mise en file des scripts de l'interface d'administration == **/
    public function admin_enqueue_scripts()
    {    
        tify_control_enqueue( 'suggest' );
        wp_enqueue_style( 'tiFyMultilingualTabooxPostTranslate', self::tFyAppUrl( get_class() ) .'/Translate.css', array(), 170217 );
    }
    
    /* = CONTROLEURS = */
    /** == Formulaire de saisie  == **/
    public function form( $post )
    {
    ?>
<table class="form-table">
    <tbody>
    <?php         
        foreach( self::$Sites as $site ) :
            $translate_id = 0;
            $translate_title = '';
            $after = "<a href=\"#tiFyMultilingual-PostTranslate--{$site->blog_id}\" class=\"close\"><span class=\"dashicons dashicons-no-alt\"></span></a>";
                        
            if( $translate_id = get_post_meta( $post->ID, '_translate_blog_'. $site->blog_id, true ) ) : 
                switch_to_blog( $site->blog_id );
                    $translate_title = get_the_title( $translate_id );
                restore_current_blog();
            endif;
            $translation = Multilingual::getTranslation( Multilingual::getLocale( $site->blog_id ) );
        ?>    
        <tr>
            <th><?php echo $translation['native_name'];?></th>
            <td>
            <?php 
                tify_control_suggest(
                    array(
                        'id'                => 'tiFyMultilingual-PostTranslate--'. $site->blog_id,
                        'class'             => 'tiFyMultilingual-PostTranslate'. ( $translate_id ? ' selected' : '' ),
                        'name'              => '_translate_blog_'. $site->blog_id,
                        'value'             => $translate_id,
                        'before'            => Multilingual::getFlag( $site->blog_id ),
                        'placeholder'       => __( 'Rechercher une traduction', 'tify' ),
                        'readonly'          => $translate_id ? true : false,
                        'ajax_action'       => 'tiFyMultilingualPostTranslate',
                        'select'            => true,
                        'selected'          => $translate_title,
                        'query_args'        => array( 'blog_id' => $site->blog_id, 'post_type' => $post->post_type )
                    )  
                );
            ?>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
    <?php
    }
    
    /** == == **/    
    public function wp_ajax()
    {
        // Arguments par defaut à passer en $_POST
        $args = array(
            'term'                => '',
            'query_args'        => array()
        );
        extract( $args );
    
        // Valeur de retour par défaut
        $response = array();
        
        // Arguments de requête WP_QUERY
        $query_args['posts_per_page'] =    -1;
        if( isset( $_POST['query_args'] ) && is_array( $_POST['query_args'] ) ) :
            $query_args = $_POST['query_args'];
        endif;
        
        // Définition du site de traduction
        if( empty( $query_args['blog_id'] ) ) :
          wp_send_json( $response );  
        endif;
          
        $blog_id =  $query_args['blog_id'];
        if( ! switch_to_blog( $blog_id ) ) :
          wp_send_json( $response );  
        endif;
            
        // Traitement des arguments de requête
        if( isset( $_POST['term'] ) )
            $term = $_POST['term'];
    
        /// Arguments de requête WP_QUERY
        $query_args['posts_per_page'] =    -1;
        if( isset( $_POST['query_args'] ) && is_array( $_POST['query_args'] ) ) :
            $query_args = $_POST['query_args'];
        endif;
        if( ! isset( $query_args['post_type'] ) ) :
            $query_args['post_type'] = 'any';
        endif;
        $query_args['s'] = $term;
            
        // Récupération des posts
        $query_post = new \WP_Query( $query_args );
        while( $query_post->have_posts() ) : $query_post->the_post();
            // Données requises
            $label             = esc_attr( strip_tags( get_the_title() ) );
            $value             = get_the_ID();
                        
            // Génération du rendu
            $render = call_user_func( array( '\tiFy\Core\Control\Suggest\Suggest', 'itemRender' ), array( 'title' => get_the_title(), 'permalink' => get_the_permalink() ) );
                
            // Valeur de retour
            $response[] = compact( 'label', 'value', 'render' );
        endwhile;
        
        // Réinitialisation
        wp_reset_query();
        restore_current_blog();
        
        wp_send_json( $response );
    }
    
    /** == == **/
    public function save_post( $post_id, $post )
    {        
        $current_blog = (int) get_current_blog_id();
        
        foreach( self::$Sites as $site ) :
            $blog_id = (int) $site->blog_id;
            if( $blog_id === $current_blog )
                continue;
           
            $exist  = ( $exist = get_post_meta( (int) $post_id, '_translate_blog_'.$blog_id, true ) ) ? (int) $exist : 0;
            $new    = ! empty( $_POST['_translate_blog_'.$blog_id] ) ? (int) $_POST['_translate_blog_'.$blog_id] : 0;
            
            if( ! $new ) :
                // Bypass
                if( ! $exist )
                    continue;
                
                delete_post_meta( $post_id, '_translate_blog_'.$blog_id, $exist );
                
                switch_to_blog( $blog_id );
                    delete_post_meta( $exist, '_translate_blog_'.$current_blog );
                restore_current_blog();
                
            elseif( $exist !== $new ) :
                update_post_meta( $post_id,'_translate_blog_'.$blog_id, $new );
                
                switch_to_blog( $blog_id );
                    if( get_post_meta( $exist, '_translate_blog_'.$current_blog, true ) ) :             
                        delete_post_meta( $exist, '_translate_blog_'.$current_blog, $post_id ); 
                    endif;    
                    update_post_meta( $new, '_translate_blog_'.$current_blog, $post_id );   
                restore_current_blog();
            endif;
        endforeach;
    }
}