<?php

	/**
	 * Show the release info. We need to know the voorziening and product for this
	 *
	 * @param  string  $content  The page content
	 * @return string  $content  The modified page content
	 */
	public function rijksreleasekalender_template_release_info( $content ) {

    $args                     = array();
    $args['programma']        = '';
    $args['productslug']      = 'ledig';
    $args['voorzieningslug']  = 'ledig';

    if ( get_query_var( $this->releasekalender_queryvar_product ) &&  get_query_var( $this->releasekalender_queryvar_voorziening ) ) {
      $args['productslug']      = get_query_var( $this->releasekalender_queryvar_product );
      $args['voorzieningslug']  = get_query_var( $this->releasekalender_queryvar_voorziening );
      $args['permalink']        = 'permalink';
      $args['year_start']       = 'year_start';
      $args['year_end']         = 'year_end';
    }


    if ( $args['productslug'] ) {
    
    	$get_producten_args = array(
        'post_type'   => 'producten',
        'name'        => $args['productslug'],
        'post_status' => 'publish',
    	);
    
    
      $productquery = new WP_Query( $get_producten_args );
      
      if ( $productquery->have_posts() ) {
        while ($productquery->have_posts()) : 
          $productquery->the_post();
    
          $metadata       = get_post_meta( get_the_id() );    	
    
          $datestring = date( 'Y-m-d H:i:s', strtotime( $metadata['product_datumIngebruikname'][0] ) );
          $datestring = get_date_from_gmt( $datestring );
          $datestring = strtotime( $datestring );
    
          $args['programma']  .= '<h1>' . get_the_title() . '</h1>';
          $args['programma']  .= '<ul>'; // 18
          if ( $datestring ) {
            $args['programma']  .= '<li>' . __( 'Ingebruikname', 'rijksreleasekalender' ) . ': ' . date_i18n( get_option( 'date_format' ), $datestring ) . '</li>';
          }
          $args['programma']  .= '<li>' . __( 'Voorziening', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_voorziening_real_id'][0] ) . '">' . get_the_title( $metadata['product_voorziening_real_id'][0] ) . '</a></li>';
    
          if ( $metadata['product_aanbieder'][0] ) {
    
            $data = maybe_unserialize( $metadata['product_aanbieder'][0] );
            
            if ( $data['website'] ) {
              $args['programma']  .= '<li>' . __( 'Aanbieder', 'rijksreleasekalender' ) . ': <a href="' . $data['website'] . '">' . $data['naam'] . '</a></li>';
            }            
            else {
              $args['programma']  .= '<li>' . __( 'Aanbieder', 'rijksreleasekalender' ) . ': ' . $data['naam'] . '</li>';
            }
    
          }
          if ( $metadata['product_doelgroep'][0] ) {
            $args['programma']  .= '<li>' . __( 'Doelgroep', 'rijksreleasekalender' ) . ': ' . $metadata['product_doelgroep'][0] . '</li>';
          }
          if ( $metadata['product_verwijzing'][0] ) {
            $args['programma']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
          }
          $args['programma']  .= '</ul>';
          $args['programma']  .= get_the_content();
          
        endwhile;
      }
      else {
      }
    
    
      wp_reset_postdata();        
    
    }


 		if ( $args['productslug'] && $args['voorzieningslug'] ) {

        $thepage            = $args['permalink'];
        $pagetype           = 'releases';
  			$get_releases_args = array(
          'post_type'   => $pagetype,

          'order'       => 'ASC',					
          'orderby'     => 'meta_value',					
          'meta_key'    => 'release_releasedatum_translated',
          
          'meta_query' => array(
          	array(
          		'key'     => 'release_product_real_id_slug',
          		'value'   => $args['productslug'],
          		'compare' => '='
          	),
          	array(
          		'key'     => 'release_voorziening_real_id_slug',
          		'value'   => $args['voorzieningslug'],
          		'compare' => '='
          	)
          )				
  			);

      $releases_query = new WP_Query( $get_releases_args );
      
      if ( $releases_query->have_posts() ) {

        $args['gerealiseerd'] = '';
        $args['vervallen']    = '';
        $args['gepland']      = '';
        
        while ($releases_query->have_posts()) : 

          $releases_query->the_post();
    
          $metadata       = get_post_meta( get_the_id() );    	
          $releasestatus  = maybe_unserialize( $metadata['release_release_status'][0] );

          $releasejaar = date( 'Y', $metadata['release_releasedatum_translated'][0] );
          
          if ( intval( $releasejaar ) < intval( $args['year_start'] ) ) {
            $args['year_start'] = $releasejaar;
          }
          if ( intval( $releasejaar ) > intval( $args['year_end'] ) ) {
            $args['year_end'] = $releasejaar;
          }

          $theurl = $thepage . $this->releasekalender_queryvar_voorziening . '/' . $args['voorzieningslug'] . '/' . $this->releasekalender_queryvar_product . '/' . $args['productslug'];
          $theid  = $releases_query->post->post_name;

          if ( $releasestatus['naam'] == 'gerealiseerd' ) {

              $args['gerealiseerd']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
              $args['gerealiseerd']  .= '<ul><li>Gerealiseerd op <span class="datum">' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</li>';
              if ( isset( $metadata['release_website'][0] ) ) {
                $args['gerealiseerd']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . $metadata['release_website'][0] . '">' . get_the_title( ) . '</a></li>';
              }
              $args['gerealiseerd']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
              $args['gerealiseerd']  .= '</ul>'; 
              $args['gerealiseerd']  .= get_the_content(); 
              $args['gerealiseerd']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
              $args['gerealiseerd']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 
              
          }
          elseif ( $releasestatus['naam'] == 'waarschuwing' ) {
          }
          elseif ( $releasestatus['naam'] == 'vervallen' ) {

              $args['vervallen']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
              $args['vervallen']  .= '<p>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</p><ul>';
              if ( isset( $metadata['product_verwijzing'][0] ) ) {
                $args['vervallen']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
              }
              $args['vervallen']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
              $args['vervallen']  .= '</ul>'; 
              $args['vervallen']  .= get_the_content(); 

              $args['vervallen']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
              $args['vervallen']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 



          }
          elseif ( ( $releasestatus['naam'] == 'gepland' ) || ( $releasestatus['naam'] == 'verwacht' ) ) {
          
              $args['gepland']  .= '<h3 id="' . $theid . '">' . get_the_title() . '</h3>';
              $args['gepland']  .= '<p>' . date_i18n( get_option( 'date_format' ), $metadata['release_releasedatum_translated'][0] ) . '</p><ul>';
              if ( isset( $metadata['product_verwijzing'][0] ) ) {
                $args['gepland']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . get_permalink( $metadata['product_verwijzing'][0] ) . '">' . get_the_title( ) . '</a></li>';
              }
              if ( isset( $metadata['release_website'][0] ) ) {
                $args['gepland']  .= '<li>' . __( 'Verwijzing', 'rijksreleasekalender' ) . ': <a href="' . $metadata['release_website'][0] . '">' . get_the_title( ) . '</a></li>';
              }
              
              $args['gepland']  .= '<li>' . __( 'Releasestatus', 'rijksreleasekalender' ) . ': ' . $releasestatus['naam'] . '</li>';
              $args['gepland']  .= '</ul>'; 
              $args['gepland']  .= get_the_content(); 

              $args['gepland']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafhankelijkheden'][0] ) ?  $metadata['releaseafhankelijkheden'][0] : "", __( 'Afhankelijkheden', 'rijksreleasekalender' ) ); 
              $args['gepland']  .= $this->get_afhankelijkheden( isset( $metadata['releaseafspraken'][0] ) ?  $metadata['releaseafspraken'][0] : "", __( 'Afspraken en standaarden', 'rijksreleasekalender' ) ); 

          }  




        endwhile;

        // Gerealiseerde releases
        $args['programma'] .=  '<div class="block">';
        $args['programma'] .=  '<h2>' . __( 'Gerealiseerde releases', 'rijksreleasekalender' ) . '</h2>';
        if ( $args['gerealiseerd'] ) {
          $args['programma'] .=  $args['gerealiseerd'];
        }
        else {
          $args['programma'] .=  '<p>' . __( 'Er zijn geen gerealiseerde releases.', 'rijksreleasekalender' ) . '</p>';
        }
        $args['programma'] .=  '</div>';
        
        // Aankomende releases
        $args['programma'] .=  '<div class="block">';
        $args['programma'] .=  '<h2>' . __( 'Aankomende releases', 'rijksreleasekalender' ) . '</h2>';
        if ( $args['gepland'] ) {
          $args['programma'] .=  $args['gepland'];
        }
        else {
          $args['programma'] .=  '<p>' . __( 'Er zijn geen aankomende releases.', 'rijksreleasekalender' ) . '</p>';
        }
        $args['programma'] .=  '</div>';

        if ( $args['vervallen'] ) {
          $args['programma'] .=  '<div class="block">';
          $args['programma'] .=  '<h2>' . __( 'Vervallen releases', 'rijksreleasekalender' ) . '</h2>';
          $args['programma'] .=  $args['vervallen'];
          $args['programma'] .=  '</div>';
        }
        
      }
      else {
        // DEBUG    		
        dovardump( $get_releases_args );
        $args['programma']      = 'WFT? product: ' . $args['productslug'] . '/ voorziening: ' . $args['voorzieningslug'];
        
      }

      wp_reset_postdata();        


		}
		else {
      // DEBUG    		
      $args['programma']      = 'WFT? product: ' . $args['productslug'] . '/ voorziening: ' . $args['voorzieningslug'];


		}

    return $args['programma'];

  }
    
