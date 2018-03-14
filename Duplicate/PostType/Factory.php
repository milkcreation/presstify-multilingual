<?php
namespace tiFy\Plugins\Multilingual\Duplicate\PostType;

class Factory extends \tiFy\Components\Duplicate\PostType\Factory
{   
    /** == == **/
    public function set_field_post_parent( $field_value )
    {
        if( ! $field_value )
            return $field_value;
        if( $translate_parent = get_post_meta( $field_value, '_translate_blog_'.$this->getInputBlogID() ) )
            return $translate_parent;
        return $field_value;
    }
    
    /** == == **/
    public function field_post_parent( $value )
    {
        if( get_post( $value ) )
            return $value;
        return 0;
    }
    
    /** == Action au moment du traitement de l'élément duplique == **/
    public function onDuplicateItem( $post_id )
    {
        parent::onDuplicateItem( $post_id );
        
        $input = $this->getInput();
        update_post_meta( $post_id, '_translate_blog_'.$this->getInputBlogID(), $input['ID']  );
    }
    
    /** == Action après la duplication de l'élément == **/
    public function afterDuplicateItem( $post_id )
    {
        parent::afterDuplicateItem( $post_id );
        
        $input = $this->getInput();
        update_post_meta( $input['ID'], '_translate_blog_'.$this->getOutputBlogID(), $post_id  );
    }
}