<?php

namespace App\Exceptions;

use DomainException;

/**
 * Una regla del negocio impide la acción ("Ya respondiste la encuesta").
 *
 * Su mensaje está escrito para el usuario y Handler lo muestra tal cual, sin
 * try/catch en el controlador. Es una clase propia y no \DomainException a
 * secas porque las librerías también lanzan DomainException con mensajes
 * técnicos que no deben llegar a la pantalla.
 */
class ReglaDeNegocioException extends DomainException {}
