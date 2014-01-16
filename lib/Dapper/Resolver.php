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

class Resolver
{
    private $deps = array();

    public function addRelationship($parent, $children)
    {
        if(!isset($this->deps[$parent])) {
            $this->deps[$parent] = array();
        }

        $this->deps[$parent] = array_merge($this->deps[$parent], $children);
        return $this;
    }

    public function resolve($nodes)
    {
        $l = $added = $traversed = array();

        $s = array_reverse($nodes);

        while(count($s) > 0) {
            $c = array_pop($s);

            if(isset($added[$c])) {
                continue;
            }

            if(!isset($this->deps[$c]) || isset($traversed[$c])) {
                array_push($l, $c);
                $added[$c] = true;
                continue;
            }

            $traversed[$c] = true;
            array_push($s, $c);
            $s = array_merge($s, $this->deps[$c]);
        }

        return $l;
    }
}
