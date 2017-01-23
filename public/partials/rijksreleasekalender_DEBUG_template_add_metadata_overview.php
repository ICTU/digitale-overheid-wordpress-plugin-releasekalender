<?php

	
  	/**
  	 * Filter for the dossier page template
  	 *
  	 * @param  string  $content  The page content
  	 * @return string  $content  The modified page content
  	 */
  	public function DEBUG_template_add_metadata_overview( $content ) {

    	global $post;
    	
    	$thepostid  = 0;
      $pagetype   = get_post_type();

      if ( ( ! get_query_var( $this->releasekalender_queryvar_product ) ) && ( ! get_query_var( $this->releasekalender_queryvar_voorziening ) ) ) {
        $thepostid = $post->ID;
      }
      else {
          
        if ( get_query_var( $this->releasekalender_queryvar_product ) &&  get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
          $pagetype   = 'releases';
    			$postid_query_args = array(
            'post_type'   => $pagetype,
            'meta_query' => array(
            	array(
            		'key'     => 'release_product_real_id_slug',
            		'value'   => get_query_var( $this->releasekalender_queryvar_product ),
            		'compare' => '='
            	),
            	array(
            		'key'     => 'release_voorziening_real_id_slug',
            		'value'   => get_query_var( $this->releasekalender_queryvar_voorziening ),
            		'compare' => '='
            	)
            )				
    			);
        }
        elseif ( get_query_var( $this->releasekalender_queryvar_product ) ) {
          $pagetype   = 'producten';
          $postid_query_args = array(
            'name'        => get_query_var( $this->releasekalender_queryvar_product ),
            'post_type'   => $pagetype,
            'post_status' => 'publish'
          );
        }
        elseif ( get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
          $pagetype   = 'voorzieningen';
          $postid_query_args = array(
            'name'        => get_query_var( $this->releasekalender_queryvar_voorziening ),
            'post_type'   => $pagetype,
            'post_status' => 'publish'
          );
        }

        $my_posts = get_posts( $postid_query_args );
        if( $my_posts ) {
          $thepostid = $my_posts[0]->ID;
        }
        wp_reset_postdata();        
      }

      
      $metadata = get_post_meta( $thepostid );    	

          // DEBUG
  		$content .= '<div class="metadata">'; 
  		$content .= '<h2>Tijdelijke testdata</h2>'; 
  		$content .= '<ul><li><a href="/voorzieningen/">Voorzieningen</a></li><li><a href="/producten/">Producten</a></li><li><a href="/releases/">Releases</a></li></ul>'; 
  		$content .= '<h3>(' . strtoupper( $pagetype ) . ') ' . get_the_title() . '</h3>'; 
  		$content .= '<p><strong>Content:</strong></p><pre>' . esc_html( get_the_content() ) . '</pre>'; 
  		
  		if ( is_array( $metadata ) ) {

    		$content .= '<ul>'; 
    		
        foreach( $metadata as $key => $value ){        
  
          if ( is_array( $value ) ) {
          
            $data = maybe_unserialize( $value[0] );
            
            if ( is_array( $data ) ) {
  
            	$content .= "<li><strong>" . $key . ':</strong><ul>'; 
        
              foreach( $data as $key1 => $value1 ){        
  
                $data2 = maybe_unserialize( $value1 );
                
                if ( is_array( $data2 ) ) {
              
                	$content .= "<li><strong>" . $key1 . ':</strong><ul>'; 
              
                  foreach( $data2 as $key2 => $value2 ){        
  
                    if ( is_array( $value2 ) ) {
                      $content .= "<li>" . $key2 . ' => ' . implode( ', ', $value2 ) . "</li>"; 
                    }
                    else {
                      $content .= "<li>" . $key2 . ' => ' . $value2 . "</li>"; 
                    }
                  }
                	$content .= "</ul></li>"; 
                }
                else {
                	$content .= "<li>" . $key1 . ' => ' . $value1 . "</li>"; 
                }
  
                
              }
            	$content .= "</ul></li>"; 
            }
            else {
              if ( $key == 'release_releasedatum_translated' || $key == 'release_updated_translated' ) {
                $releasedate = implode( '', $value );
              	$content .= "<li><strong>" . $key . '</strong> => ' . date( 'j F Y -  H:i:s', $releasedate ) . " (" . $releasedate . ")</li>"; 
              }
              else {
              	$content .= "<li><strong>" . $key . '</strong> => ' . implode( ', ', $value ) . "</li>"; 
              }
            }
          }
          else {
          	$content .= "<li>" . $key . ' => ' . $value . "</li>"; 
          }
  
        }

    		$content .= '</ul>'; 
    		
  		}
        
  		$content .= '</div>'; 
      //============================================================================================================

  		return $content;
  		
  	}
  