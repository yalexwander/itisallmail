<?php

namespace ItIsAllMail\CoreTypes;


final class PostResult {

    public PostStatus $status;
    public ?string $postId;
    public ?string $error;
    public mixed $response;

    public function __construct(PostStatus $status, string $postId = null, string $error = null, mixed $response = null)
    {
        $this->status = $status;
        $this->postId = $postId;
        $this->error = $error;
        $this->response = $response;
    }
}
