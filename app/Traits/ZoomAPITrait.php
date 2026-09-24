<?php

namespace App\Traits;

use App\Models\Meeting;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait ZoomAPITrait
{
    public $client;
    public $jwt;
    public $headers;
    public $zoomUrl;
    public $meetingType = 2;
    public $hostVideo = 1;
    public $participantVideo = 1;

    public function __construct()
    {
        //define value for all functions
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->jwt,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $this->zoomUrl = env('ZOOM_API_URL', '');
    }

    public function generateZoomToken(): string
    {
        $key = env('ZOOM_API_KEY', '');
        $secret = env('ZOOM_API_SECRET', '');
        $payload = [
            'iss' => $key,
            'exp' => strtotime('+1 minute'),
        ];
        // create an new jwttoken for zoom api
        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    public function toZoomTimeFormat(string $dateTime): string
    {
        // get date and convert into the correct date which is need to zoom api
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toZoomTimeFormat : ' . $e->getMessage());
            return '';
        }
    }

    public function meetingObject($topic, $start_time, $agenda, $duration, $id = null): array
    {
        $data = [
            'topic' => $topic,
            'type' => $this->meetingType,
            'start_time' => $this->toZoomTimeFormat($start_time),
            'duration' => $duration,
            'agenda' => $agenda,
            'timezone' => 'Asia/Kolkata',
            'settings' => [
                'host_video' => $this->hostVideo === "1",
                'participant_video' => $this->participantVideo === "1",
                'waiting_room' => true,
            ],
        ];

        if (isset($id)) {
            $data["id"] = $id;
        }

        return $data;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function createMeeting($email, $topic, $start_time, $agenda, $duration): array
    {
        $path = 'users/' . $email . '/meetings';
        $data = $this->meetingObject($topic, $start_time, $agenda, $duration);
        $body = [
            'headers' => $this->headers,
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
        ];

        $data = [
            "success" => false,
            "data" => null,
        ];
        try {
            $response = $this->client->post($this->zoomUrl . $path, $body);
            $data["success"] = $response->getStatusCode() === 201;
            $data["data"] = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $error) {
        }
        return $data;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function updateMeeting($id, $topic, $start_time, $duration, $agenda): array
    {

        $path = 'meetings/' . $id;
        $data = $this->meetingObject($topic, $start_time, $agenda, $duration, $id);
        $body = [
            'headers' => $this->headers,
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
        ];
        $response = $this->client->patch($this->zoomUrl . $path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data' => json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getMeeting($id): array
    {
        $path = 'meetings/' . $id;
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body' => json_encode([], JSON_THROW_ON_ERROR),
        ];

        $response = $this->client->get($this->zoomUrl . $path, $body);
        return [
            'success' => $response->getStatusCode() === 200,
            'data' => json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function createUser($email): array
    {
        $path = "users";
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body' => json_encode([
                "action" => "create",
                "user_info" => [
                    "email" => $email,
                    "type" => 1
                ]], JSON_THROW_ON_ERROR),
        ];

        $data = [
            "success" => false,
            "data" => null,
        ];
        try {
            $response = $this->client->post($this->zoomUrl . $path, $body);
            $data["success"] = $response->getStatusCode() === 200;
            $data["data"] = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            if($e->getCode() == 409){
                $data['success'] = false;
                $data['data'] = 409;
            }
        }
        return $data;
    }

    public function trainerAllocationToCreateMeeting($trainer_allocation, $date): void
    {
        $zoom_user = null;
        if (isset($trainer_allocation->trainer)) {
            if ($trainer_allocation->trainer->zoom) {
                $zoom_user = $trainer_allocation->trainer->official_email_id;
            } else {
                $get_response = $this->createUser($trainer_allocation->trainer->official_email_id);
                if ($get_response['data'] == 409) {
                    $trainer_allocation->trainer->zoom = true;
                    $trainer_allocation->trainer->save();
                    $zoom_user = $trainer_allocation->trainer->official_email_id;
                }
                if ($get_response['success']) {
                    $trainer_allocation->trainer->zoom = true;
                    $trainer_allocation->trainer->save();
                    $zoom_user = $trainer_allocation->trainer->official_email_id;
                }
            }
            $from = Carbon::createFromFormat('m/d/Y H:i', $date . $trainer_allocation->class_start);
            $exitsmeeting = Meeting::where("allowcated_id", $trainer_allocation->id)->whereDate('meeting_time', $from)->first();
            if (!isset($exitsmeeting)) {
                $to = Carbon::createFromFormat('m/d/Y H:i', $date . $trainer_allocation->class_end);
                $diff_in_hours = $to->diffInMinutes($from);
                $topic = $trainer_allocation->trainer->trainer_name . "-" . $trainer_allocation->level->grade . "-" . $trainer_allocation->getSchool->school_name;
                $meeting = $this->createMeeting($zoom_user, $topic, $from, "", $diff_in_hours);
                if (isset($meeting["data"]["id"])) {
                    Meeting::create([
                        "allowcated_id" => $trainer_allocation->id,
                        "school_id" => $trainer_allocation->school_id,
                        "trainer_id" => $trainer_allocation->trainer_id,
                        "zoom_id" => $meeting["data"]["id"],
                        "meeting_time" => $meeting["data"]["start_time"],
                        "join_url" => $meeting["data"]["join_url"],
                        "password" => "",
                    ]);
                }
            }
        }
    }

}
