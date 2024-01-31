<?php
/*
Plugin Name: REST API Clientes
Description: Este plugin agrega un endpoint a la API REST de WordPress para manipular datos de la tabla de clientes.
Version: 1.0
Author: Tu Nombre
*/
// Función para crear la tabla de clientes al activar el plugin
function crear_tabla_clientes() {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';
    // Definir la estructura de la tabla
    $sql = "CREATE TABLE $tabla_clientes (
        id INT NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255),
        correo_electronico VARCHAR(255),
        telefono VARCHAR(20),
        PRIMARY KEY (id)
    )";
    // Incluir el archivo necesario para ejecutar dbDelta()
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    // Crear o modificar la tabla en la base de datos
    dbDelta( $sql );
}
// Agregar la acción para crear la tabla de clientes al activar el plugin
register_activation_hook( __FILE__, 'crear_tabla_clientes' );

// Función para obtener un cliente por ID
function obtener_cliente( $request ) {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';
    $id = $request['id'];
    // Obtener el cliente de la base de datos
    $cliente = $wpdb->get_row( "SELECT * FROM $tabla_clientes WHERE id = $id" );
    return $cliente;
}
//
function obtener_clientes( $request ) {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';

    $sql = "SELECT * FROM $tabla_clientes";
    $clientes = $wpdb->get_results($sql);
    return $clientes;
}

// Función para crear un cliente
function crear_cliente( $request ) {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';
    $cliente = array(
        'nombre' => $request->get_param( 'nombre' ),
        'correo_electronico' => $request->get_param( 'correo_electronico' ),
        'telefono' => $request->get_param( 'telefono' ),
    );
    // Insertar el cliente en la base de datos
    $wpdb->insert( $tabla_clientes, $cliente );
    // Devolver el ID del nuevo cliente
    return $wpdb->insert_id;
}
// Función para actualizar un cliente por ID
function actualizar_cliente( $request ) {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';
    $id = $request['id'];
    $cliente = array(
        'nombre' => $request->get_param( 'nombre' ),
        'correo_electronico' => $request->get_param( 'correo_electronico' ),
        'telefono' => $request->get_param( 'telefono' ),
    );
    // Actualizar el cliente en la base de datos
    $wpdb->update( $tabla_clientes, $cliente, array( 'id' => $id ) );
    // Devolver la cantidad de filas afectadas
    return $wpdb->rows_affected;
}
// Función para eliminar un cliente por ID
function eliminar_cliente( $request ) {
    global $wpdb;
    $tabla_clientes = $wpdb->prefix . 'clientes';
    $id = $request['id'];
    // Eliminar el cliente de la base de datos
    $wpdb->delete( $tabla_clientes, array( 'id' => $id ) );
    // Devolver la cantidad de filas afectadas
    return $wpdb->rows_affected;
}
// Función para registrar el endpoint de la API REST
function registrar_endpoint_rest_clientes() {
    register_rest_route( 'clientes/v1', '/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'obtener_cliente',
        'args' => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );

    register_rest_route( 'clientes/v1', '/todos', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'obtener_clientes',
        
        'permission_callback' => function($request){
            if(current_user_can('edit_posts')) return true;
        }
    ));

    register_rest_route( 'clientes/v1', '/*', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'crear_cliente',
    ) );
    register_rest_route( 'clientes/v1', '/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'actualizar_cliente',
        'args' => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
    register_rest_route( 'clientes/v1', '/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'eliminar_cliente',
        'args' => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
}


function my_custom_rest_cors() {
  remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
  add_filter( 'rest_pre_serve_request', function( $value ) {
    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Methods: GET' );
    header( 'Access-Control-Allow-Credentials: true' );
    header( 'Access-Control-Expose-Headers: Link', false );

    return $value;
  } );
}


add_action( 'rest_api_init', 'registrar_endpoint_rest_clientes','my_custom_rest_cors' );