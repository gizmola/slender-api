<?php

namespace App\Test;

use Dws\Utils;

class ControllerTestCase extends TestCase
{

    public function doGetSingleResponseTest($url,$id)
    {
        $response = $this->call('GET', "{$url}/{$id}");
        $this->assertEquals(200, $response->getStatusCode());
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        return $response[array_keys($response)[0]][0];
    }
    public function doPostResponseTest($url, $input)
    {
        $response = $this->call('POST', $url, array(), array(), array(), json_encode($input));
        if ($response->getStatusCode() == 400) {
            var_dump($response->getContent()); 
            die();   
        }
        $this->assertEquals(201, $response->getStatusCode());
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        return $response[array_keys($response)[0]][0];
    }

    public function doPutResponseTest($url, $id, $input)
    {
        $url = "$url/{$id}";
        $response = $this->call('PUT', $url, array(), array(), array(), json_encode($input));
        if ($response->getStatusCode() == 400) {
            var_dump($response->getContent()); 
            die();   
        }
        $this->assertEquals(201, $response->getStatusCode());
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        return $response[array_keys($response)[0]][0];
    }

    public function doDeleteResponseTest($url, $id)
    {
        $url = "$url/{$id}";
        $response = $this->call('DELETE', $url, array(), array(), array(), '');
        $this->assertEquals(200, $response->getStatusCode());
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey("messages", $response);
        $this->assertEquals('ok', $response['messages'][0]);
    }

    public function doCanUpdateParentTest($child, $parent, $update, $embeddedData = null)
    {

        /* 
        * first we insert a parent
        */
        $response = $this->doPostResponseTest($parent['endpoint'], $parent['input']);
        $parentId =  $response["_id"];
        $this->refreshApplication();
        /* 
        * next we send the child data
        * with the parent id
        */
        $child['input']["_parents"] = [$parent['key'] => [$parentId]];
        $response = $this->doPostResponseTest($child['endpoint'], $child['input']);
        $childId =  $response["_id"];
        $this->refreshApplication();
        /*
        * now lets get the parent and make sure the child was embedded
        */
        unset($child['input']["_parents"]);
        $response = $this->doGetSingleResponseTest($parent['endpoint'], $parentId);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        $this->removeUntestableKeys($response[$child['key']]);
        /* 
        * if post request processing 
        * occurs allow manual override of data
        */
        $childData = $embeddedData ?: $child['input'];
        if (Utils\Arrays::isIndexed($response[$child['key']]) && !Utils\Arrays::isIndexed($childData)) {
            $childData = [$childData];    
        }
        $this->assertSame($childData, $response[$child['key']]);
        $this->refreshApplication();
        
        /*
        * next lets update
        */

        foreach ($update as $k => $v) {
            $child['input'][$k] = $v;    
        }
        
        $response = $this->doPutResponseTest($child['endpoint'], $childId, $child['input']);
        $this->refreshApplication();
        /*
        * and check that the parent was updated      
        */
        $response = $this->doGetSingleResponseTest($parent['endpoint'], $parentId);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        $embedded = $response[$child['key']];

        if (!Utils\Arrays::isIndexed($embedded)) {
            $this->checkEmbeddedUpdates($embedded, $update);
        } else {
            foreach ($embedded as $e) {
                $this->checkEmbeddedUpdates($e, $update);    
            }
        }

        /*
        * next delete
        */
        $this->doDeleteResponseTest($child['endpoint'], $childId);
        $this->refreshApplication();
        /*
        * finally assert that
        * the data has been removed
        */
        $response = $this->doGetSingleResponseTest($parent['endpoint'], $parentId);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        $this->assertTrue(empty($response[$child['key']]));

    }

    public function doInsertWithChild($child, $parent, $embeddedData = null)
    {
        /*
        * first insert child data
        */
        $response = $this->doPostResponseTest($child['endpoint'], $child['input']);
        $childId =  $response["_id"];
        $this->refreshApplication();
        /* 
        * next we send the parent data with the child ids
        */
        $parent['input']["_children"] = [$child['key'] => [$childId]];
        $response = $this->doPostResponseTest($parent['endpoint'], $parent['input']);
        //$childId =  $response["_id"];
        $this->refreshApplication();
        /*
        * now lets make sure the child was embedded
        */
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        /* 
        * if post request processing 
        * occurs allow manual override of data
        */
        $childData = $embeddedData ?: $child['input'];
        if (Utils\Arrays::isIndexed($response[$child['key']]) && !Utils\Arrays::isIndexed($childData)) {
            $childData = [$childData];    
        }
        $this->removeUntestableKeys($response[$child['key']]);
        $this->assertSame($childData, $response[$child['key']]); 

    }

    public function doUpdateWithParent($child, $parent, $update)
    {

        /**
        * first we insert the parent
        */
        $response = $this->doPostResponseTest($parent['endpoint'], $parent['input']);
        $parentId = $response['_id'];
        /**
        * next we insert the child w/o the parent
        */
        $this->refreshApplication();
        $response = $this->doPostResponseTest($child['endpoint'], $child['input']);
        $childId = $response['_id'];
        /**
        * then we update the child w/ parent
        */
        $update['_parents'] = [$parent['key'] => [$parentId]];
        $this->refreshApplication();
        $response = $this->doPutResponseTest($child['endpoint'], $childId, $update);
        /**
        * now retrieve the parent
        * and check that this child was embedded
        */
        $this->refreshApplication();
        $response = $this->doGetSingleResponseTest($parent['endpoint'], $parentId);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        $this->assertInternalType('array', $response[$child['key']]);
        $childData = (Utils\Arrays::isIndexed($response[$child['key']])) ?
            $response[$child['key']][0] :
            $response[$child['key']];
        $this->assertArrayHasKey('_id', $childData);
        $this->assertSame($childId, $childData['_id']); 

    }

    public function doUpdateWithChild($child, $parent, $update)
    {

        /**
        * first we insert the child
        */
        $response = $this->doPostResponseTest($child['endpoint'], $child['input']);
        $childId = $response['_id'];
        /**
        * next we insert the parent w/o the child
        */
        $this->refreshApplication();
        $response = $this->doPostResponseTest($parent['endpoint'], $parent['input']);
        $parentId = $response['_id'];
        /**
        * then we update the parent w/ child
        */
        $update['_children'] = [$child['key'] => [$childId]];
        $this->refreshApplication();
        $response = $this->doPutResponseTest($parent['endpoint'], $parentId, $update);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey($child['key'], $response);
        $this->assertInternalType('array', $response[$child['key']]);
        /**
        * check that child exists
        */
        $childData = (Utils\Arrays::isIndexed($response[$child['key']])) ?
            $response[$child['key']][0] :
            $response[$child['key']];
        $this->assertArrayHasKey('_id', $childData);
        $this->assertSame($childId, $childData['_id']); 

    }


    private function removeUntestableKeys(&$array, $keys = ['_id', 'created_at','updated_at'])
    {

        if (!Utils\Arrays::isIndexed($array)) {
            
            foreach ($keys as $key) {
                unset($array[$key]);   
            }
                
        } else {
            
            for ($i = 0; $i < count($array); $i++) {

                foreach ($keys as $key) {
                    unset($array[$i][$key]);   
                }      
            
            }
        }

    }

    private function checkEmbeddedUpdates($embedded, $update)
    {
        foreach ($update as $k => $v) {
            if (is_array($embedded)) {
                $this->assertArrayHasKey($k, $embedded);
                $this->assertSame($v, $embedded[$k]); 
            } else {
                //not sure how to handle non-array yet
            }
        }
    }

}