<?php 	
$testimonials['start'][]  	= '<div class="entry-content item element type'. $inc .'">';
$testimonials['titles'][] 	= 		"{$thumbnail}<h2><a href='".get_permalink()."'>". get_the_title() .'</a></h2>';
$testimonials['body'][] 	= 		'<p class="testimonial-text">' . get_the_content() . '</p><p class="testimonial-client-name"><cite>' . $cite . '</cite></p></div>'; //**
$testimonials['end'][]		= '</div>'; //debug the loop output and verify why there is a need for the extra closing div at the end of the line above.