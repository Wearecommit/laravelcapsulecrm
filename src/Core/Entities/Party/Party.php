<?php

namespace CapsuleCRM\Core\Entities\Party;

use CapsuleCRM\Core\CapsulecrmManager;
use Illuminate\Support\Facades\Validator;
//Updates here
class Party extends CapsulecrmManager
{
    /**
     *
     * @var PrepareDataForParty
     */
    private $prepareDataFactory;
    /**
     * Party constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = "parties";
        $this->prepareDataFactory=new PrepareDataForParty();
    }

    /**
     * Register new user(party) on capsule CRM or update existing user
     *
     * @param array $data array of user data
     * @param string $tag
     * @return \App\Services\Capsulecrm\ClientException|\App\Services\Capsulecrm\Response|\App\Services\Capsulecrm\type|int
     * @throws \Exception
     */
    public function register(array $data, $tag)
    {
        $data['tags'][] = $tag;
        $this->validation($data);
        $valid = $this->validateUniqueEmail($data['email']);

        if ($valid === true) {
            return $this->create($data);
        } else {
            $body = $this->prepareDataFactory->setData(['tags' => $data['tags']])->tags()->getBody();

            return $this->update($valid, $body);
        }
    }

    /**
     * Store Party
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
     * @param array $data
     * @param array $fields
     *
     * @return \CapsuleCRM\Core\Response|\GuzzleHttp\Exception\ClientException|int
     */
    public function create(array $data, array $fields = [])
    {
        $body = $this->prepareDataFactory->setData($data)
            ->name()
            ->type()
            ->email()
            ->tags()
            ->address()
            ->phone()
            ->custom_fields($fields)
            ->getBody();

        return $this->post($body);
    }

    /**
     *
     * @param int $id
     * @param array $data
     * @return int|ClientException|Response
     */
    public function update($id, array $data)
    {
        $url = $this->url."/$id";

        return $this->put($data, $url);
    }

    /**
     * Validate data
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function validation(array $data)
    {
        $validation = Validator::make($data, [
            'email' => 'required|email',
            'tags' => 'required|array',
            'tags.*' => 'string',
        ]);

        if ($validation->errors()->isEmpty()) {
            return true;
        } else {
            throw new \Exception(array_first($validation->errors()->getMessages())[0], 406);
        }
    }

    /**
     * Validate if user email exist on Capsule
     *
     * @param $email string
     * @return mix (true if email not exist otherwise return user id)
     */
    public function validateUniqueEmail($email)
    {
        $response= $this->search($email);
        if ($response!=false) {
            return $response[0]->id;
        }

        return true;
    }

    /**
     * Search For Party by any $filter on Capsule
     *
     * @param $filter string
     * @return mix (false if email not exist otherwise return user id)
     */
    public function search($filter)
    {
        $query = $this->url.'/search?'."q=$filter";
        $response = $this->get(false, $query);
        if (count($response->parties)) {
            return $response->parties;
        }

        return false;
    }

    /**
     * Get Parties
     *
     * Returns a list of parties that
     * are in Capsule CRM.
     *
     * @param int $page
     * @return bool
     */
    public function all($page = 1) {
        $query = $this->url .'?page=' . $page;
        $response = $this->get(false, $query);
        if(count($response->parties)) {
            return $response->parties;
        }
        return false;
    }

    /**
     * Get Field Definitions
     *
     * Returns the custom fields that are available
     * in the Capsule CRM instance.
     *
     * @param int $page
     * @return bool
     */
    public function fields($page = 1) {
        $query = $this->url .'/fields/definitions?page=' . $page;
        $response = $this->get(false, $query);
        if(count($response->definitions)) {
            return $response->definitions;
        }
        return false;
    }
}
