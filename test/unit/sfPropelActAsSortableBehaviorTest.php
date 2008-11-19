<?php
/*
 * This file is part of the sfPropelActAsSortableBehavior package.
 * 
 * (c) 2007 Francois Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Unit tests for the sfPropelActAsSortableBehavior plugin.
 *
 * Despite running unit tests, we use the functional tests bootstrap to take advantage of propel
 * classes autoloading...
 * 
 * In order to run the tests in your context, you have to copy this file in a symfony test directory
 * and configure it appropriately (see the "configuration" section at the beginning of the file)
 *  
 * @author   Francois Zaninotto <francois.zaninotto@symfony-project.com>
 */

// configuration
// -- an existing application name
$app = 'frontend';

// -- the model class the tests should use
$sortable_class = 'sfSimpleForumCategory';
$sortable_field = 'rank';

// -- path to the symfony project where the plugin resides
$sf_path = dirname(__FILE__).'/../../../..';
 
// bootstrap
include($sf_path . '/test/bootstrap/functional.php');

// create a new test browser
$browser = new sfTestBrowser();
$browser->initialize();

// initialize database manager
$databaseManager = new sfDatabaseManager();
$databaseManager->initialize();

$con = Propel::getConnection();

$sortable_peer_class = $sortable_class.'Peer';

// cleanup database
call_user_func(array($sortable_peer_class, 'doDeleteAll'));

// register behavior on test object
sfPropelBehavior::add($sortable_class, array('act_as_sortable' => array(
  'column' => $sortable_field,
  'new_element_position' => 'bottom'
)));




// Now we can start to test
$t = new lime_test(90, new lime_output_color());

$t->diag('new methods');
$methods = array(
  'getPosition',
  'setPosition',
  'swapWith',
  'getNext',
  'getPrevious',
  'isFirst',
  'isLast',
  'moveUp',
  'moveDown',
  'moveToPosition',
  'moveToBottom',
  'moveToTop',
  'insertAtPosition',
  'insertAtBottom',
  'insertAtTop'
);
foreach ($methods as $method)
{
  $t->ok(is_callable($sortable_class, $method), sprintf('Behavior adds a new %s() method to the object class', $method));
}

$t->diag('sfPropelActAsSortableBehavior::getMaxPosition()');
$t->is(sfPropelActAsSortableBehavior::getMaxPosition($sortable_peer_class), 0, 'sfPropelActAsSortableBehavior::getMaxPosition($peer_class) returns 0 when there is no record');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$item2 = new $sortable_class();
$item2->save();
$item3 = new $sortable_class();
$item3->save();
$t->is(sfPropelActAsSortableBehavior::getMaxPosition($sortable_peer_class), 3, 'sfPropelActAsSortableBehavior::getMaxPosition($peer_class) returns the current maximum rank');

$t->diag('sfPropelActAsSortableBehavior::retrieveByPosition()');
$item = sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 2);
$t->is($item->getPosition(), 2, 'sfPropelActAsSortableBehavior::retrieveByPosition($peer_class, $rank) returns the object of rank $rank');

$t->diag('sfPropelActAsSortableBehavior::doSelectOrderByPosition()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$t->is_deeply(sfPropelActAsSortableBehavior::doSelectOrderByPosition($sortable_peer_class), array(), 'sfPropelActAsSortableBehavior::doSelectOrderByPosition($peer_class) returns an empty array when there is no record');
$item1 = new $sortable_class();
$item1->setPosition(2);
$item1->save();
$id1 = $item1->getId();
$item2 = new $sortable_class();
$item2->setPosition(3);
$item2->save();
$id2 = $item2->getId();
$item3 = new $sortable_class();
$item3->setPosition(1);
$item3->save();
$id3 = $item3->getId();
$items = sfPropelActAsSortableBehavior::doSelectOrderByPosition($sortable_peer_class);
$t->is($items[0]->getId(), $id3, 'sfPropelActAsSortableBehavior::doSelectOrderByPosition($peer_class) returns objects ordered by position');
$t->is($items[1]->getId(), $id1, 'sfPropelActAsSortableBehavior::doSelectOrderByPosition($peer_class) returns objects ordered by position');
$t->is($items[2]->getId(), $id2, 'sfPropelActAsSortableBehavior::doSelectOrderByPosition($peer_class) returns objects ordered by position');

$t->diag('sfPropelActAsSortableBehavior::doSort()');
$t->is(sfPropelActAsSortableBehavior::doSort($sortable_peer_class, array(
  $id1 => 3,
  $id2 => 2,
  $id3 => 1,
  )), true, 'sfPropelActAsSortableBehavior::doSort($peer_class, $order) returns true when successful');
$items = sfPropelActAsSortableBehavior::doSelectOrderByPosition($sortable_peer_class);
$t->is($items[0]->getId(), $id3, 'sfPropelActAsSortableBehavior::doSort($peer_class, $order) reorders objects');
$t->is($items[1]->getId(), $id2, 'sfPropelActAsSortableBehavior::doSort($peer_class, $order) reorders objects');
$t->is($items[2]->getId(), $id1, 'sfPropelActAsSortableBehavior::doSort($peer_class, $order) reorders objects');

$t->diag('setPosition() and getPosition()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item = new $sortable_class();
$item->setPosition(123);
$t->is($item->getByname($sortable_field, BasePeer::TYPE_FIELDNAME), 123, 'setPosition() sets position on the field passed to the behavior constructor');
$t->is($item->getPosition(), 123, 'getPosition() gets position on the field passed to the behavior constructor');

$t->diag('save()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$id1 = $item1->getId();
$t->is($item1->getPosition(), 1, 'save() sets the rank to the current max rank plus one');
$item2 = new $sortable_class();
$item2->save();
$id2 = $item2->getId();
$t->is($item2->getPosition(), 2, 'save() sets the rank to the current max rank plus one');
$item3 = new $sortable_class();
$item3->save();
$id3 = $item3->getId();
$t->is($item3->getPosition(), 3, 'save() sets the rank to the current max rank plus one');
$item4 = new $sortable_class();
$item4->setPosition(12);
$item4->save();
$t->is($item4->getPosition(), 12, 'save() does not override the rank if it is already set');
$item4->delete();

$t->diag('getNext() and getPrevious()');
$t->is($item2->getNext()->getId(), $id3, 'getNext() returns the next object in rank order');
$t->isa_ok($item3->getNext(), 'NULL', 'getNext() returns null when called on the object of highest rank');
$t->is($item2->getPrevious()->getId(), $id1, 'getPrevious() returns the previous object in rank order');
$t->isa_ok($item1->getPrevious(), 'NULL', 'getPrevious() returns null when called on the object of first rank');

$t->diag('isFirst() and isLast()');
$t->is($item1->isFirst(), true, 'isFirst() returns true for the first object in order');
$t->is($item2->isFirst(), false, 'isFirst() returns false for other objects');
$t->is($item3->isFirst(), false, 'isFirst() returns false for other objects');
$t->is($item3->isLast(), true, 'isLast() returns true for the last object in order');
$t->is($item1->isLast(), false, 'isLast() returns false for other objects');
$t->is($item1->isLast(), false, 'isLast() returns false for other objects');

$t->diag('moveUp() and moveDown()');
$t->is($item1->moveUp(), false, 'moveUp() returns false when called on the object of lowest rank');
$t->is_deeply($item2->moveUp(), array(2, 1), 'moveUp() returns an array of swapped positions when called successfully');
$t->is(sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 1)->getId(), $id2, 'moveUp() moves the object up (decreases the rank)');
$t->is(sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 2)->getId(), $id1, 'moveUp() moves the previous object down');
$t->is($item3->moveDown(), false, 'moveDown() returns false when called on the object of highest rank');
$t->is_deeply(sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 2)->moveDown(), array(2, 3), 'moveDown() returns an array of swapped positions when called successfully');
$t->is(sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 3)->getId(), $id1, 'moveDown() moves the object down (increases the rank)');
$t->is(sfPropelActAsSortableBehavior::retrieveByPosition($sortable_peer_class, 2)->getId(), $id3, 'moveDown() moves the next object up');

$t->diag('delete()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$id1 = $item1->getId();
$item2 = new $sortable_class();
$item2->save();
$id2 = $item2->getId();
$item3 = new $sortable_class();
$item3->save();
$id3 = $item3->getId();
$item2->delete();
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$t->is($item3->getPosition(), 2, 'delete() decreases the rank of the following records by one');
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$t->is($item1->getPosition(), 1, 'delete() does no decrease the rank of the previous records in the order');
$item1->delete();
$item3->delete();

$t->diag('swapWith()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$item2 = new $sortable_class();
$item2->save();
$item3 = new $sortable_class();
$item3->save();
$item2->swapWith($item1);
$t->is($item1->getPosition(), 2, 'swapWith() exchanges the rank of two adjacent records');
$t->is($item2->getPosition(), 1, 'swapWith() exchanges the rank of two adjacent records');

$t->diag('moveToPosition(), moveToTop(), moveToBottom()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$id1 = $item1->getId();
$item2 = new $sortable_class();
$item2->save();
$id2 = $item2->getId();
$item3 = new $sortable_class();
$item3->save();
$id3 = $item3->getId();
$item4 = new $sortable_class();
$item4->save();
$id4 = $item4->getId();
$item3->moveToPosition(3);
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$item4 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id4);
$t->is($item1->getPosition(), 1, 'moveToPosition(samePos) does not alter anything');
$t->is($item2->getPosition(), 2, 'moveToPosition(samePos) does not alter anything');
$t->is($item3->getPosition(), 3, 'moveToPosition(samePos) does not alter anything');
$t->is($item4->getPosition(), 4, 'moveToPosition(samePos) does not alter anything');
$item2->moveToPosition(1);
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$t->is($item2->getPosition(), 1, 'moveToPosition() moves the element to the right position');
$t->is($item1->getPosition(), 2, 'moveToPosition() alters the position of the other elements');
$t->is($item3->getPosition(), 3, 'moveToPosition() does no alter the rank of the next records in the order');
$item3->moveToTop();
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$item4 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id4);
$t->is($item3->getPosition(), 1, 'moveToTop() moves the element to the right position');
$t->is($item1->getPosition(), 3, 'moveToTop() alters the position of the other elements');
$t->is($item2->getPosition(), 2, 'moveToTop() alters the position of the other elements');
$t->is($item4->getPosition(), 4, 'moveToTop() does no alter the rank of the next records in the order');
$item2->moveToBottom();
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$item4 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id4);
$t->is($item2->getPosition(), 4, 'moveToBottom() moves the element to the bottom position');
$t->is($item1->getPosition(), 2, 'moveToBottom() alters the position of the other elements');
$t->is($item4->getPosition(), 3, 'moveToBottom() alters the position of the other elements');
$t->is($item3->getPosition(), 1, 'moveToBottom() does no alter the rank of the previous records in the order');

$t->diag('insertAtPosition(), insertAtTop(), insertAtBottom()');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
$item1 = new $sortable_class();
$item1->save();
$id1 = $item1->getId();
$item2 = new $sortable_class();
$item2->save();
$id2 = $item2->getId();
$item3 = new $sortable_class();
$item3->save();
$id3 = $item3->getId();
$item4 = new $sortable_class();
$item4->insertAtPosition(2);
$id4 = $item4->getId();
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$t->is($item1->getPosition(), 1, 'insertAtPosition() does no alter the rank of the previous records in the order');
$t->is($item4->getPosition(), 2, 'insertAtPosition() inserts the element at the right position');
$t->is($item2->getPosition(), 3, 'insertAtPosition() alters the position of the next elements');
$t->is($item3->getPosition(), 4, 'insertAtPosition() alters the position of the next elements');
$item5 = new $sortable_class();
$item5->insertAtTop();
$id5 = $item5->getId();
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$item4 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id4);
$t->is($item5->getPosition(), 1, 'insertAtTop() inserts the element at the top position');
$t->is($item1->getPosition(), 2, 'insertAtTop() alters the position of the next elements');
$t->is($item4->getPosition(), 3, 'insertAtTop() alters the position of the next elements');
$t->is($item2->getPosition(), 4, 'insertAtTop() alters the position of the next elements');
$t->is($item3->getPosition(), 5, 'insertAtTop() alters the position of the next elements');
$item6 = new $sortable_class();
$item6->insertAtBottom();
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$item3 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id3);
$item4 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id4);
$item5 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id5);
$t->is($item6->getPosition(), 6, 'insertAtBottom() inserts the element at the bottom position');
$t->is($item5->getPosition(), 1, 'insertAtTop() does no alter the rank of the other elements');
$t->is($item1->getPosition(), 2, 'insertAtTop() does no alter the rank of the other elements');
$t->is($item4->getPosition(), 3, 'insertAtTop() does no alter the rank of the other elements');
$t->is($item2->getPosition(), 4, 'insertAtTop() does no alter the rank of the other elements');
$t->is($item3->getPosition(), 5, 'insertAtTop() does no alter the rank of the other elements');

$t->diag('save() with `new_element_position` to top');
call_user_func(array($sortable_peer_class, 'doDeleteAll'));
sfConfig::set('propel_behavior_act_as_sortable_'.$sortable_class.'_new_element_position', 'top');
$item1 = new $sortable_class();
$item1->save();
$id1 = $item1->getId();
$t->is($item1->getPosition(), 1, 'save() sets the rank to the first rank');
$item2 = new $sortable_class();
$item2->save();
$id2 = $item2->getId();
$t->is($item2->getPosition(), 1, 'save() sets the rank to the first rank');
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$t->is($item1->getPosition(), 2, 'save() shifts the rank of all elements');
$item3 = new $sortable_class();
$item3->save();
$id3 = $item3->getId();
$t->is($item3->getPosition(), 1, 'save() sets the rank to the first rank');
$item1 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id1);
$item2 = call_user_func(array($sortable_peer_class, 'retrieveByPk'), $id2);
$t->is($item1->getPosition(), 3, 'save() shifts the rank of all elements');
$t->is($item2->getPosition(), 2, 'save() shifts the rank of all elements');
sfConfig::set('propel_behavior_act_as_sortable_'.$sortable_class.'_new_element_position', 'bottom');