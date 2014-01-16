<?php

/*
 * This file is part of the Dapper package.
 *
 * (c) Matthew Conger-Eldeen <mceldeen@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dapper;

class ResolverTest
    extends \PHPUnit_Framework_TestCase
{
    protected $resolver;

    protected function setUp()
    {
        ini_set('max_execution_time', 0);
        $this->resolver = new Resolver();
    }

    protected function tearDown()
    {
        unset($this->resolver);
    }

    public function testDedupe()
    {
        $results = $this->resolver->resolve(array('a', 'a', 'b', 'b', 'c'));

        $expected = array('a', 'b', 'c');

        // check to see if they have the same number of elements
        $this->assertCount(count($expected), $results);

        // check to see if they have the same elements
        $this->assertCount(0, array_diff($expected, $results));
        $this->assertCount(0, array_diff($results, $expected));
    }

    public function testHeirarchyOrder()
    {
        $deps = array(
            'a' => array('b', 'c'),
            'b' => array('d', 'e'),
            'f' => array('b', 'g'),
        );

        foreach($deps as $parent => $children) {
            $this->resolver->addRelationship($parent, $children);
        }

        $nodes = $this->resolver->resolve(array('a', 'f', 'e')); // expect all children to come before their parents

        foreach($nodes as $parentIndex => $node) {
            if(!isset($deps[$node])) {
                continue;
            }

            $children = $deps[$node];
            foreach($children as $child) {
                $childIndex = array_search($child, $nodes);
                $this->assertGreaterThan($childIndex, $parentIndex, "$node should come after $child");
            }
        }
    }

    public function testCircular()
    {
        $deps = array(
            'a' => array('b'),
            'b' => array('a'),
        );

        foreach($deps as $parent => $children) {
            $this->resolver->addRelationship($parent, $children);
        }

        ini_set('max_execution_time', 1);
        $this->resolver->resolve(array('a','b'));

        $this->assertTrue(true); // we're just making sure it doesn't hang
    }
}
