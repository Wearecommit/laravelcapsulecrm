<?php

namespace CapsuleCRM\Core\Entities\Party;

class PrepareDataForParty
{

    /**
     * the body of data
     *
     * @var array
     */
    private $body;

    /**
     * the data from user
     *
     * @var array
     */
    private $data;

    /**
     * prepare tags
     *
     * @param array $data
     */
    public function tags()
    {
        if (array_key_exists('tags', $this->data)) {
            foreach ($this->data['tags'] as $tag) {
                $this->body['party']['tags'][] = [
                    'name' => $tag
                ];
            }
        }
        
        return $this;
    }

    /**
     * Prepare typegetBody
     *
     * @return $this
     */
    public function type()
    {
        $this->body['party']['type'] = valueExist($this->data, 'type', 'person');

        return $this;
    }
    
    /**
     * prepare email
     *
     * @return $this
     */
    public function email()
    {
        if (array_has($this->data, 'email')) {
            $this->body['party']['emailAddresses'] = [
                ['type' => 'Work', 'address' => $this->data['email']]
            ];
        }
        
        return $this;
    }

    /**
     * Prepare name
     *
     * @return $this
     */
    public function name()
    {
        $this->body['party']['firstName'] = valueExist($this->data, 'first_name', valueExist($this->data, 'name', $this->data['email']));
        $this->body['party']['lastName'] = valueExist($this->data, 'last_name', '');

        return $this;
    }

    /**
     * Custom Fields
     *
     * Parses the data and attributes any
     * custom fields into the custom fields
     * section.
     *
     * Example of $fields array:
     *
     *  $fields = [
     *      'field_id_from_capsule' => [
     *          'id' => 'field_id_from_capsule',
     *          'name' => 'name_from_capsule',
     *          'field' => 'the_field_you_are_looking_for'
     *      ]
     *  ];
     *
     * @param array $fields
     * @return PrepareDataForParty
     */
    public function custom_fields(array $fields) {
        $this->body['party']['fields'] = [];
        foreach($this->data AS $data) {
            foreach($fields AS $id => $field) {
                if(valueExists($this->data, $field['field'])) {
                    $this->body['party']['fields'][] = [
                        'value' => valueExist($this->data, $field['field'], ''),
                        'definition' => [
                            'id' => $field['id']
                        ]
                    ];
                }
            }
        }
        return $this;
    }
    
    /**
     * Set name
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->body = [
            'party' => []
        ];
        
        return $this;
    }

    /**
     * get Body
     *
     * @return type
     */
    public function getBody()
    {
        return $this->body;
    }
}
