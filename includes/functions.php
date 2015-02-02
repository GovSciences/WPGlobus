<?php

/**
���� ����� 
������������ ������ pre_insert_term ( taxonomy.php:2769 )
�� ����� �������� $term
������� ����� ���� ����
- {:en}WPGlobusQA category_name EN{:}{:ru}WPGlobusQA category_name RU{:}
- WPGlobusQA category_name EN
- WPGlobusQA category_name RU

term 
1-�� ������ ������� �� name
2-�� ������ ������� �� slug
3-�� ������ �� ������� � ����� ������ ����� term

��� SELECT ������ � ����� �� ����� name ��� ������ ������ 

��������� ������ ��������
����� �������� � �������� term �������� - WPGlobus � �������, ��� ����� ������ ����� ����, �� �� �� ���������
��������� ����� ������ ��� ����� ������������� �����.
������ ���� SELECT �������� ���, ����� �������� � �������� ������ 
{:zz}WPGLobus{:}
[:zz]WPGLobus
<!--:zz-->WPGLobus<!--:-->
���� ������ ��� ����� 'WPGLobus' , �� ����� ������ ������ � ���� name �� ������ ����.

����� ���� � SELECT ���� ���������� ������� wp_term_taxonomy ����� ��������� $taxonomy.

��� ���� ���� ����� ������� ���� � ������������� �� ���� �������� �����,
���� ����� ��� qT � ������� [:zz]  �� ������ �������� 

*/

add_filter( 'pre_insert_term', 'wpglobus_insert_term', 10, 2 );
function wpglobus_insert_term( $term, $taxonomy ) {
		
	//error_log($term);	
	//error_log($taxonomy);
	
	global $wpdb;
	
	$sql = "SELECT name FROM wp_terms WHERE name LIKE '%$term%'";
	
	$var = $wpdb->get_var( $sql );
	
	// error_log('name : ' . $var);
	
	return $var;
}
//wp_set_object_terms( 546, array('WPGlobusQA category_name RU'), 'category'); 
