<?php
/**
 * WPEC_EP_Settings class
 *
 * @class 		WPEC_EP_Settings
 * @since		1.0.0
 * @category	Class
 * @author 		PerfectSolution
 */
class WPEC_EP_Settings
{
    /**
     * Update an option if a change post request is made
     * 
     * @static
     * @param string $setting > the option key string
     */
    public static function update_on_post( $setting, $json_decode = false )
    {        
        if( isset( $_POST[$setting] ) ) 
        {
            /*if($json_decode){
                $obj = str_replace("\\", '', $_POST[$setting]);
                var_dump($obj);
                var_dump(json_decode($obj));
                
                exit;
            }*/
            
            update_option( $setting, $_POST[$setting]);
        }
    }
    
    
    /**
     * Gets an option. Wrapper for get_option.
     * 
     * @static
     * @param  string $setting
     * @param  string [$default = TRUE] > the value to default to if the setting is not available
     * @return string > the setting value
     */
    public static function get( $setting, $default = '' ) 
    {
        return get_option( $setting , $default );
    }
    
    
    /**
     * Prints out a settings field. Used on the settings page.
     * 
     * @static
     * @param  string [$name = '']       the field name
     * @param  string [$value = '']      the field value
     * @param  string [$type = 'hidden'] the field type
     * @return string   the composed input field
     */
    public static function field( $name = '', $value = '', $type = 'hidden' ) 
    {
        return "<input type=\"$type\" name=\"$name\" value=\"$value\"/>\n";
    }
    
    /**
     * Prins out a field description / hint. Used on the settings page.
     * 
     * @static
     * @param  string $message > the message to show
     * @return string
     */
    public static function field_hint( $message )
    {
        return '<small style="line-height:14px;display:block;padding:2px 0 6px;">' . $message . '</small>';
    }
}