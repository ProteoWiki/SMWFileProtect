<?php
/*
 * SMWFileProtect Class
 *
 * Copyright (C) 2011-2014  Toni Hermoso Pulido <toniher@cau.cat>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */


class SMWFileProtect
{
	private $dbr;
	private $db_page, $db_page_links;


	public function __construct() {
		$this->dbr = wfGetDB( DB_SLAVE );
	}
	
	/**
	 * The main function call to display the backward navigation element for
	 * the page  $pageid
	 */
	public function executeImageRefer( $title, $user ) {
		
		global $wgContLang;

		#Get PageID
		$pageid =$title->getArticleID();

		//Allow permission
		$allowtag = 0;

		$username = $wgContLang->getNsText(NS_USER).":".$user->getName();
		
		// Allow to group
		if (self::groupcheck($user)) {
			return true;
		}

		#Get list of linked pages
		$listReferer = array();
		$listReferer=$this->loadListReferer($pageid);

		//Allow permission
		$allowtag = 0;
		$allowtagp = -1;

		//Count linked pages
		$nbReferer= count( $listReferer );

		//Two groups of properties
		$allowprops = 0;
		$allowusers = 0;
		global $SMWFileProtectReferUsers;
		global $SMWFileProtectReferProps;

		#Check if variables exist
		if ( !isset( $SMWFileProtectReferUsers ) ) {
			if ( !isset( $SMWFileProtectReferUsers ) ) {
				#If not defined -> allow fully -> CAREFUL
				return(true);
			}
			else {
				if( count( $SMWFileProtectReferProps ) < 1 ) {
					#If not defined -> allow fully -> CAREFUL
					return(true);
				}
				else {
					// Block
					$allowprops=1;
				}
			}
		}

		else {
		
			#If not string value -> allow fully -> CAREFUL
			if( count( $SMWFileProtectReferUsers ) < 1 ) {
				return(true);
			}
			else {
			
				if ( !isset( $SMWFileProtectReferProps ) ) {
					
					// No block
					$allowprops = 0;
				}
				
				else {

					
					if( count( $SMWFileProtectReferProps ) > 0 ) {
						
						// Block
						$allowprops = 1;
					}
				}
			
				// Block userwise
				$allowusers = 1;
			}
		}

		if ( $nbReferer > 0 ) {
			
			foreach ( $listReferer as $pageReferer ) {
				
				//number of results
				$numresu = 0;
				$numresp = 0;

				//First query case

				// Query each linked page for the properties 

				if ( $allowusers > 0 ) {
					
					foreach ( $SMWFileProtectReferUsers as $propUser ) {
					
						$propUser = str_replace(" ", "_", $propUser);
						$properties_to_display = array();
						$properties_to_display[0] = $propUser;
						$results = self::getQueryResults( "[[$pageReferer]][[$propUser::+]]", $properties_to_display, false );
				
						$viewerlist = array();

						while ( $row = $results->getNext() ) {
							$stat = $row[1];
							$ostat = $stat->getNextObject();
							if ($ostat) {
								$viewerlist = explode(",", $ostat->getLongWikiText());
							}
							$numresu++;
						}
	
						#If User is specifically allowed
						if (in_array( $username, $viewerlist ) ) {
							$allowtag = 1;
						}
					}
					
					#If no semantic content -> Allow
					if ($numresu == 0) { $allowtag = 1;}
					
					if ( $allowprops > 0 ) {
						
						foreach ( $SMWFileProtectReferProps as $propProp ) {
							
							$propProp = str_replace(" ", "_", $propProp);
							$properties_to_display = array();
							$properties_to_display[0] = $propProp;
							$results = self::getQueryResults( "[[$pageReferer]][[$propProp::+]]", $properties_to_display, false );

							$visible = false;
							
							while ( $row = $results->getNext() ) {
								$stat = $row[1];
								$visible = $stat->getNextObject()->getLongWikiText();

								$numresp++;
							}
							
												#If report is made visible for the requester
							if ($visible == 'true') {
								$allowtagp = 1;
							}
							
							else {
								$allowtagp = 0;
							}
							
							#If no semantic content -> Allow
							if ($numresp == 0) { $allowtagp = -1;}
							
						}

					}
					
				} else {
					
					if ( $allowprops > 0 ) {
						
						foreach ( $SMWFileProtectReferProps as $propProp ) {
							
							$propProp = str_replace(" ", "_", $propProp);
							$properties_to_display = array();
							$properties_to_display[0] = $propProp;
							$results = self::getQueryResults( "[[$pageReferer]][[$propProp::+]]", $properties_to_display, false );

							$visible = false;
							
							while ( $row = $results->getNext() ) {
								$stat = $row[0];
								$visible = $stat->getNextObject()->getLongWikiText();

								$numresp++;
							}
							
							#If report is made visible for the requester
							if ($visible == 'true') {
								$allowtagp = 1;
							}
							
							else {
								$allowtagp = 0;
							}
							
							#If no semantic content -> Allow
							if ($numresp == 0) { $allowtagp = -1;}
							
						}
						
					} 
				}

			}
		} else {
			$allowtag = 1;
		}

		if ( $allowtag >0 ) { 
			
			if ( $allowtagp > -1 ) {

				if ( $allowtagp > 0 ) {
					return true;
				}

				else {
					return false;
				}

			}

			return true; 

		} else { return false; }
  	}
  	
	/**
	 * Load the referers list for the article $pageid
	 */
	 
	public function loadListReferer( $pageid ) {
	
		if (! is_numeric($pageid)) return array();
		//$SQL2 = "select g.il_from from ".$this->db_image_links." g, ".$this->db_page." p where g.il_to=p.page_title and p.page_id=?";

		$table = array( 'imagelinks', 'page' );
		$vars = array( 'il_from' );
		$conds = array( 'il_to=page_title', 'page_id='.$pageid );
		$options = array();
		$condoptions = array();

		$result = $this->dbr->select( $table, $vars, $conds, 'SMWFileProtect::loadListReferer', $options, $condoptions );

		// $tbs=$this->dbr->safeQuery($SQL2,$pageid);
	
		$listReferer=array();
		$i=0;
		
		foreach ( $result as $row ) {
			$title = Title::newFromId( $row->il_from );
			if ( ( get_class( $title )=="Title" ) && ( $title->exists() ) ) {
				$listReferer[$i]=$title->getPrefixedText();
				$i++;
			}
		}

		return( $listReferer );
	}


	/**
	 * This function returns to results of a certain query
	 * Thank you Yaron Koren for advices concerning this code
	 * @param $query_string String : the query
	 * @param $properties_to_display array(String): array of property names to display
	 * @param $display_title Boolean : add the page title in the result
	 * @return TODO
	 */
	static function getQueryResults( $query_string, $properties_to_display, $display_title ) {
		
		// We use the Semantic MediaWiki Processor
		// $smwgIP is defined by Semantic MediaWiki, and we don't allow
		// this file to be sourced unless Semantic MediaWiki is included.
		global $smwgIP;
		include_once( $smwgIP . "/includes/SMW_QueryProcessor.php" );
		
		$params = array();
		$inline = true;
		$printlabel = "";
		$printouts = array();
		
		// add the page name to the printouts
		if ( $display_title ) {
			$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_THIS, $printlabel );
			array_push( $printouts, $to_push );
		}
		
		// Push the properties to display in the printout array.
		foreach ( $properties_to_display as $property ) {
			if ( class_exists( 'SMWPropertyValue' ) ) { // SMW 1.4
				$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_PROP, $printlabel, SMWPropertyValue::makeProperty( $property ) );
			} else {
				$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_PROP, $printlabel, Title::newFromText( $property, SMW_NS_PROPERTY ) );
			}
			array_push( $printouts, $to_push );
		}
		
		if ( version_compare( SMW_VERSION, '1.6.1', '>' ) ) {
			SMWQueryProcessor::addThisPrintout( $printouts, $params );
			$params = SMWQueryProcessor::getProcessedParams( $params, $printouts );
			$format = null;
		}
		else {
			$format = 'auto';
		}
		
		$query = SMWQueryProcessor::createQuery( $query_string, $params, $inline, $format, $printouts );
		$results = smwfGetStore()->getQueryResult( $query );
		
		return $results;
	}
	

	function groupcheck( $user ) {

		global $SMWFileProtectRights;
		foreach ( $SMWFileProtectRights as $grp ) {
			if( in_array( $grp,$user->getEffectiveGroups()) ) {
				return true;
			}
		}
	}

}

?>
