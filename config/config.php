<?php

// register behavior hooks
sfPropelBehavior::registerHooks('act_as_sortable', array(
  ':save:pre'   => array('sfPropelActAsSortableBehavior', 'preSave'),
  ':delete:pre' => array('sfPropelActAsSortableBehavior', 'preDelete'),  
));

sfPropelBehavior::registerMethods('act_as_sortable', array (
  array ('sfPropelActAsSortableBehavior', 'getPosition'),
  array ('sfPropelActAsSortableBehavior', 'setPosition'),
  array ('sfPropelActAsSortableBehavior', 'swapWith'),
  array ('sfPropelActAsSortableBehavior', 'getNext'),
  array ('sfPropelActAsSortableBehavior', 'getPrevious'),
  array ('sfPropelActAsSortableBehavior', 'isFirst'),
  array ('sfPropelActAsSortableBehavior', 'isLast'),
  array ('sfPropelActAsSortableBehavior', 'moveUp'),
  array ('sfPropelActAsSortableBehavior', 'moveDown'),
  array ('sfPropelActAsSortableBehavior', 'moveToPosition'),
  array ('sfPropelActAsSortableBehavior', 'moveToBottom'),
  array ('sfPropelActAsSortableBehavior', 'moveToTop'),
  array ('sfPropelActAsSortableBehavior', 'insertAtPosition'),
  array ('sfPropelActAsSortableBehavior', 'insertAtBottom'),
  array ('sfPropelActAsSortableBehavior', 'insertAtTop'),
));
