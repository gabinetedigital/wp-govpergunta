<?php

define('WPGOVP_TYPE_POST','contrib_govpergunta');
define('WPGOVP_TYPE_POST_campo1','_thumbnail_id');
define('WPGOVP_TYPE_POST_campo2','wp_govpergunta_score');
define('WPGOVP_TYPE_POST_campo3','wp_govpergunta_contribuicao_relacionada');
define('WPGOVP_TYPE_POST_campo4','wp_govpergunta_resposta_govpergunta');

define('WPGOVP_RESULTS_PER_PAGE', 10);

function WPGOVP_log($io, $msg){
	$fp = fopen("/var/www/wordpress/log/xmlrpc.log","a+");
	$date = gmdate("Y-m-d H:i:s ");
	$iot = ($io == "I") ? " Input: " : " Output: ";
	fwrite($fp, "\n\n".$date.$iot.$msg);
	fclose($fp);
}

function wp_govpergunta_get_Contribuicoes($args){

	global $wpdb;

	$page    = $args[3]["page"] ? $args[3]["page"] : '0';    //page 
	$sortby  = $args[3]["sortby"] ? $args[3]["sortby"] : 'score'; //order by
	$order   = $args[3]["order"] ? $args[3]["order"] : 'DESC'; // order by 
	$postID  = $args[3]["postID"] ? $args[3]["postID"] : NULL; // post_Id
	$offset  = $args[3]["offset"] ? $args[3]["offset"] : '0';
	$perpage = $args[3]["perpage"] ? $args[3]["perpage"] : '10000';
	$totalporpage = $args[3]["totalporpage"] ? $args[3]["totalporpage"] : WPGOVP_RESULTS_PER_PAGE; // paginacao
	$filterprincipal = $args[3]["principal"] ? $args[3]["principal"] : NULL;

	$sortfields = array('date' => 'contrib_'.WPGOVP_TYPE_POST_campo2.' ');
	
	if ($sortby[0] === '-') {
        $order = 'ASC';
        $sortby = substr($sortby, 1, strlen($sortby));
    }

	if ($filterprincipal){
		if ($filterprincipal == 'S'){
			$filter = " AND contrib_".WPGOVP_TYPE_POST_campo3." = 'PRINCIPAL' ";
			if ($postID) {
				$filter .= "AND ID = ".$postID;
			}
		} else {
			if ($postID) {
				$filter = " AND contrib_".WPGOVP_TYPE_POST_campo3." = ".$postID;
			}
		}
	}


	if (isset($sortfields[$sortby])) {
        $sortfield = $sortfields[$sortby];
    } else {
        $sortfield = 'contrib_'.WPGOVP_TYPE_POST_campo2.'';
    }						

	$sql = "	SELECT 	x.*
				FROM 	(
						SELECT 
							p.*, 
        					GROUP_CONCAT(IF(m.meta_key='".WPGOVP_TYPE_POST_campo1."', m.meta_value, NULL)) contrib_".WPGOVP_TYPE_POST_campo1.", 
        					GROUP_CONCAT(IF(m.meta_key='".WPGOVP_TYPE_POST_campo2."', m.meta_value, NULL)) contrib_".WPGOVP_TYPE_POST_campo2.",  
        					GROUP_CONCAT(IF(m.meta_key='".WPGOVP_TYPE_POST_campo3."', m.meta_value, NULL)) contrib_".WPGOVP_TYPE_POST_campo3.",
        					GROUP_CONCAT(IF(m.meta_key='".WPGOVP_TYPE_POST_campo4."', m.meta_value, NULL)) contrib_".WPGOVP_TYPE_POST_campo4."
						FROM    
							wp_posts p 
        					left join wp_postmeta m on p.id = m.post_id 
						WHERE
							post_type = '".WPGOVP_TYPE_POST."'
						AND post_status = 'publish'
						group   
							by p.id
						) x
				WHERE   
					post_type = '".WPGOVP_TYPE_POST."'
					$filter 
				ORDER	
					by 	$sortfield $order";
    
    $sql = $wpdb->prepare($sql . " LIMIT %d, %d", array($offset, $perpage));
    WPGOVP_log("O", $sql);
    //$sql = $wpdb->prepare($sql);
    $listing = $wpdb->get_results($sql, ARRAY_A);
    
    $sql = $wpdb->prepare("SELECT COUNT(*) from ($sql) x");
    $count = $wpdb->get_var($sql);
    
    $ret = array();
    foreach ($listing as $c) {
		
		// tema
		//$c["category"] = "";
		//foreach((get_the_category($c["ID"])) as $category) {
		//	$c["category"] = $category->cat_ID; 
		//} 
		
		$ret[] = $c;
    }
    return array($count, $ret); 
 	  
}


add_filter('xmlrpc_methods', 'wp_govpergunta_xmlrpc_methods');
function wp_govpergunta_xmlrpc_methods($methods)
{
	//declarar array de metodos
	//$methods[<chamada do metodo pelo rpc>] = <metodo que deve ser executado>;
	$methods['wpgovp.getContribuicoes']	= 'wp_govpergunta_get_Contribuicoes';
	return $methods;
}

?>