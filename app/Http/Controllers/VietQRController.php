<?php
namespace App\Http\Controllers;

use Firebase\JWT\ExpiredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class VietQRController extends Controller
{
    private string $validUsername;
    private string $validPassword;
    private string $secretKey;

    public function __construct()
    {
        $this->validUsername = config('app.vietqr_username');
        $this->validPassword = config('app.vietqr_password');
        $this->secretKey = config('app.vietqr_secretkey');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tokenGenerateAction(Request $request): JsonResponse
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return Response::json(["error" => "Authorization header is missing or invalid"], 400);
        }

        // Giải mã Base64 từ Authorization header
        $base64Credentials = substr($authHeader, 6);
        $credentials = base64_decode($base64Credentials);
        list($username, $password) = explode(':', $credentials);

        if ($username === $this->validUsername && $password === $this->validPassword) {
            $token = $this->createJwtToken($username);
            return Response::json([
                "access_token" => $token,
                "token_type" => "Bearer",
                "expires_in" => 300
            ]);
        } else {
            return Response::json(["error" => "Invalid credentials"], 401);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionSyncAction(Request $request): JsonResponse
    {
        $authHeader = $request->header('Authorization');
        $bearerPrefix = 'Bearer ';
        if (!$authHeader || !str_starts_with($authHeader, $bearerPrefix)) {
            return Response::json(["error" => "Authorization header is missing or invalid"], 401);
        }

        $token = substr($authHeader, strlen($bearerPrefix));

        // Xác thực JWT Token
        if (!$this->validateToken($token)) {
            return $this->respondWithError("Invalid or expired token", 401);
        }

        $data = $request->input();

        Log::info($data);

        if (!$data) {
            return $this->respondWithError("Invalid JSON data", 400);
        }

        try {
            // Sinh mã refTransactionId
            $refTransactionId = "GeneratedRefTransactionId_" . time();

            $response = [
                "error" => false,
                "errorReason" => null,
                "toastMessage" => "Transaction processed successfully",
                "object" => [
                    "reftransactionid" => $refTransactionId
                ]
            ];

            return $this->respond($response, 200);
        } catch (\Exception $e) {
            return $this->respondWithError("Transaction failed: " . $e->getMessage(), 400);
        }
    }

    /**
     * @param $token
     * @return bool
     */
    private function validateToken($token): bool
    {
        try {
            JWT::decode($token, new Key($this->secretKey, 'HS512'));
            return true;
        } catch (ExpiredException|\Exception $e) {
            return false;
        }
    }

    /**
     * @param $message
     * @param $statusCode
     * @return JsonResponse
     */
    private function respondWithError($message, $statusCode): JsonResponse
    {
        return Response::json([
            "error" => true,
            "errorReason" => $message,
            "toastMessage" => $message,
            "object" => null
        ], $statusCode);;
    }

    /**
     * @param $data
     * @param $statusCode
     * @return JsonResponse
     */
    private function respond($data, $statusCode): JsonResponse
    {
        return Response::json($data, $statusCode);;
    }

    /**
     * @param $username
     * @return string
     */
    private function createJwtToken($username): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 300; // Token hết hạn sau 300 giây
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'username' => $username
        ];

        return JWT::encode($payload, $this->secretKey, 'HS512');
    }
}
