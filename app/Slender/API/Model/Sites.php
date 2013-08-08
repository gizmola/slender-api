<?php

namespace Slender\API\Model;

use Slug\Slugifier;

class Sites extends \Slender\API\Model\BaseModel
{

    protected $collectionName = 'sites';

    protected $timestamp = true;

    protected $schema = [
        'url'       => ['required', 'url',  
                            'label' => 'URL', 
                            'description' => 'Site URL startting with http://',
                            'placeholder' => 'http://www.example.com'
                        ],
        'title'     => ['required'],
        'slug'     => [],

        // 'date'  => [
        //         'type' => 'date',
        //         'format' => 'MM/dd/yyyy hh:mm'
        // ],

        // 'textarea' => [
        //         'type' => 'textarea',
        //         'label' => 'Big Text', 
        //         'description' => 'Textarea',
        //         'placeholder' => 'Text Text text'
        // ],

        // 'typeahead' => [
        //     'type' => 'typeahead',
        //     'data' => ['aaa', 'abb', 'bbb', 'ccc']
        // ],

        // 'dropdown' =>[
        //     'type' => 'dropdown',
        //     'data' => [
        //         '0' => 'Zero',
        //         '1' => 'One',
        //         '3' => 'Three'
        //     ],
        //     'default' => '3'
        // ],

        // 'radio' =>[
        //     'type' => 'radio',
        //     'inline' => true,
        //     'data' => [
        //         '0' => 'Zero',
        //         '1' => 'One',
        //         '3' => 'Three'
        //     ],
        //     'default' => '1'
        // ],

        // 'checkbox' =>[
        //     'type' => 'checkbox',
        //     'inline' => true,
        //     'data' => [
        //         '0' => 'Zero',
        //         '1' => 'One',
        //         '3' => 'Three'
        //     ],
        //     'default' => '0'
        // ],
        // 'tags' =>[
        //     'type' => 'tag',
        // ],
        // 'filename' => ['string'],
        
    ];


    public function insert(array $data)
    {

        if(!isset($data['slug']) || !$data['slug']){
            $data['slug'] = (new Slugifier)->slugify(strtolower($data['title']));
        }

        return parent::insert($data);
    }
}
