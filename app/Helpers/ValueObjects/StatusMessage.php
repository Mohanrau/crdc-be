<?php
namespace App\Helpers\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class StatusMessage implements Arrayable
{
    protected
        $status,
        $messages
    ;

    /**
     * StatusMessage constructor.
     *
     * Creates an value object with a status and message
     *
     * @param bool $status
     * @param string|string[] $messages
     */
    public function __construct (bool $status, $messages) {
        $this->status = $status;

        $this->messages = is_string($messages) ? [ 'default' => $messages] : $messages;
    }

    /**
     * @return bool
     */
    public function getStatus () : bool
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getMessages () : array
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            "status" => $this->status,
            "messages" => (object) $this->messages
        ];
    }
}