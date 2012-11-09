<?php 
/*
 Plugin Name: WP Governador Pergunta
Plugin URI: http://localhost/wordpress2/
Description: Plugin Wordpress Governador Pergunta, desenvolvido pela PROCERGS. Controle de contribuicoes do Governador Pergunta.
Version: 1.0.0
Author: Felipe/Cristiane/Leo
Author URI: http://www.procergs.com.br
*/

/*  Copyright 2012  FELIPE PASQUALI  (email : felipe-pasquali@procergs.rs.gov.br)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WPGovPerg{

	public function ativar(){
		add_option('wp_govperg', '');
	}
	public function desativar(){
		delete_option('wp_govperg');
	}
}

$pathPlugin = substr(strrchr(dirname(__FILE__),DIRECTORY_SEPARATOR),1).DIRECTORY_SEPARATOR.basename(__FILE__);

// Função ativar
register_activation_hook( $pathPlugin, array('WPGovPerg','ativar'));

// Função desativar
register_deactivation_hook( $pathPlugin, array('WPGovPerg','desativar'));


add_action( 'init', 'wp_govperg_contribuicao' );

function wp_govperg_contribuicao() {
	$labels = array(
			'name' => _x( 'Contribuições', 'contribuicao' ),
			'singular_name' => _x( 'Contribuição', 'contribuicao' ),
			'add_new' => _x( 'Adicionar Nova', 'contribuicao' ),
			'all_items' => _x('Contribuições', 'contribuicao'),
			'add_new_item' => _x( 'Adicionar Nova Contribuição', 'contribuicao' ),
			'edit_item' => _x( 'Editar Contribuição', 'contribuicao' ),
			'new_item' => _x( 'Nova Contribuição', 'contribuicao' ),
			'view_item' => _x( 'Visualizar Contribuição', 'contribuicao' ),
			'search_items' => _x( 'Pesquisar Contribuições', 'contribuicao' ),
			'not_found' => _x( 'Nenhuma contribuição encontrada', 'contribuicao' ),
			'not_found_in_trash' => _x( 'Nenhuma contribuição encontrada na lixeira', 'contribuicao' ),
			'parent_item_colon' => _x( 'Contribuição pai:', 'contribuicao' ),
			'menu_name' => _x( 'Governador Pergunta', 'contribuicao'),
	);
	$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'author', 'comments', 'thumbnail'),
			'taxonomies' => array( 'category', 'tema_govpergunta' ),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 100,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'			
	);
	register_post_type( 'contrib_govpergunta', $args );	
}


add_action( 'init', 'wp_govperg_taxonomy_temas' );

function wp_govperg_taxonomy_temas() {
	$labels = array(
			'name' => _x( 'Temas', 'temas' ),
			'singular_name' => _x( 'Tema', 'temas' ),
			'search_items' => _x( 'Pesquisar Temas', 'temas' ),
			'popular_items' => _x( 'Temas populares', 'temas' ),
			'all_items' => _x( 'Todos os Temas', 'temas' ),
			'parent_item' => _x( 'Tema Pai', 'temas' ),
			'parent_item_colon' => _x( 'Tema Pai:', 'temas' ),
			'edit_item' => _x( 'Editar Tema', 'temas' ),
			'update_item' => _x( 'Atualizar Tema', 'temas' ),
			'add_new_item' => _x( 'Adicionar Novo Tema', 'temas' ),
			'new_item_name' => _x( 'Novo Tema', 'temas' ),
			'separate_items_with_commas' => _x( 'Separate temas with commas', 'temas' ),
			'add_or_remove_items' => _x( 'Adicionar ou Remover Temas', 'temas' ),
			'choose_from_most_used' => _x( 'Selecionar Temas mais utilizados', 'temas' ),
			'menu_name' => _x( 'Temas', 'temas' ),
	);
	$args = array(
			'labels' => $labels,
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_tagcloud' => true,
			'hierarchical' => true,
			'rewrite' => true,
			'query_var' => true
	);
	register_taxonomy('tema_govpergunta', array('contrib_govpergunta'), $args );
}

$prefix = 'wp_govpergunta_';


global $wpdb;

$results = $wpdb->get_results("select wp.ID as id, wp.post_title as title from wp_posts wp inner join wp_postmeta wpm on wp.id = wpm.post_id where wp.post_type = 'contrib_govpergunta' and wpm.meta_key='wp_govpergunta_contribuicao_relacinada' and wpm.meta_value='PRINCIPAL'");

$combo_contrib = array();
$combo_contrib["PRINCIPAL"] = "CONTRIBUICAO PRINCIPAL";
if ($results) 
{	
	foreach ($results as $row) {
		$chave = $row->id;
		$contrib = $row->title;
		$combo_contrib[$chave] = $contrib;
	}
} 


global $meta_boxes_govpergunta;

$meta_boxes_govpergunta = array();

$meta_boxes_govpergunta[] = array(
		'id' => $prefix.'parent_of',
		'title' => 'Informaçoes Gerais',
		'pages' => array('contrib_govpergunta'),
		'context'=> 'normal',
		'priority'=> 'high',
		'fields' => array(
				array(
						'name' 		=> 'Score',
						'id' 		=> $prefix . 'score',
						'type'	 	=> 'text',
						'desc'		=> 'Score da contribuiçao'
				),				
				array(
					'name' => 'Contribuiçao Relacionada',
					'id'   => $prefix.'contribuicao_relacionada',
					'type' => 'select',
					'options' => $combo_contrib,
					'multiple' => false,
					'std'  => array( 'PRINCIPAL' )					
				),
				array(
						'name' 		=> 'Resposta do Governador',
						'id' 		=> $prefix . 'resposta_govpergunta',
						'type'	 	=> 'wysiwyg',
						'desc'		=> 'Resposta do Governador'
				)

		)
);

function wp_govpergunta_register_meta_boxes()
{
	global $meta_boxes_govpergunta;

	if ( class_exists( 'RW_Meta_Box' ) )
	{
		foreach ( $meta_boxes_govpergunta as $meta_box )
		{
			new RW_Meta_Box( $meta_box );
		}
	}
}

add_action( 'admin_init', 'wp_govpergunta_register_meta_boxes' );


include_once('wp-govpergunta-xmlrpc.php');

?>