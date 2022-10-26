<?php

namespace App\Utils;

use Google\Service\Books;
use Google\Service\Books\VolumeVolumeInfo;
use Google_Client;

class GoogleBooksApiUtils
{
    /**
     * @var Books
     */
    private $gbapi;


    public function __construct()
    {
        $client = new Google_Client();
        $this->gbapi = new Books($client);
    }

    public function gettingVolumeInfoByIsbn(string $isbn): VolumeVolumeInfo
    {
        // Using the ISBN as search parameter,
        // the Google Books API should return only one item
        $volumeInfo = $this->gbapi->volumes->listVolumes("isbn:$isbn")->getItems()[0]->getVolumeInfo();

        return $volumeInfo;
    }
}
