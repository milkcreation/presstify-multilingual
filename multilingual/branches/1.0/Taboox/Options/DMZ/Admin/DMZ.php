<?php
namespace tiFy\Plugins\Multilingual\Taboox\Options\DMZ\Admin;

class DMZ extends \tiFy\Core\Taboox\Admin
{

    /* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
    public function admin_init()
    {
        \register_setting($this->page, 'tify_multilingual_dmz');
        \register_setting($this->page, 'tify_multilingual_backto_origin');
    }
    
    /** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{
	    tify_control_enqueue( 'switch' );	
	}

    /* = FORMULAIRE DE SAISIE = */
    public function form()
    {
        ?>
        <table class="form-table">
        	<tbody>
        		<tr>
        			<th scope="row">
    					<?php _e( 'Page par défaut', 'tify' ); ?>
    					<em style="display: block; font-weight: 300;">
    						<?php _e( "Affichée si la page de traduction équivalente n'existe pas", 'tify' ); ?>
    					</em>
        			</th>
        			<td>
    					<?php
                        wp_dropdown_pages(
                            array(
                                'depth'             =>  -1,
                                'selected'          => get_option('tify_multilingual_dmz'),
                                'name'              => 'tify_multilingual_dmz',
                                'show_option_none'  => __( 'Aucune page choisie', 'tify' )
                            )    
                        );
                        ?>
        			</td>
        		</tr>
        		<tr>
        			<th scope="row">
    					<?php _e( "Afficher le bouton de redirection vers le contenu d'origine", 'tify' ); ?>
        			</th>
        			<td>
    					<?php
                        tify_control_switch(
                            array(
                                'name'				=> 'tify_multilingual_backto_origin',
                    			'checked' 			=> get_option('tify_multilingual_backto_origin', 'on' )
                            )
                        );
                        ?>
        			</td>
        		</tr>
        	</tbody>
        </table>
        <?php
    }
}