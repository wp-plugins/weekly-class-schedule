<?php
/**
 * @file
 * Defines the IWcsForm interface.
 */

interface IWcsForm
{
  /**
  * Starts the form processing chain.
  *
  * @param string $op
  *  Form operation ('submit', 'delete', etc...)
  */
  public function begin( $op, $id );
  
  /**
  * Validate the form.
  *
  * If successful, call $this->process( $op ) to proceed with form processing.
  * Else, call $this->setFormError() and return.
  *
  * @param string $op
  *  Form operation ('submit', 'delete', etc...)
  */
  public function validate( $op, $id );
  
  /**
  * Process the actual form data.
  *
  * @pararm string $op
  * 	Form operation ('submit', 'delete', etc...)
  */
  public function process( $op, $id );
}