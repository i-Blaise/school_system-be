$school = App\Models\School::firstOrCreate(['slug' => 'demo-test-123'], ['name' => 'Demo Test School 123']);
$user = App\Models\User::firstOrCreate(
    ['email' => 'test-admin@demo.com'],
    [
        'school_id' => $school->id,
        'name' => 'Test Admin',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]
);

echo "--- API TEST START ---\n\n";

echo "1. POST /api/auth/lookup (Testing Email Routing Context)\n";
$req = Illuminate\Http\Request::create('/api/auth/lookup', 'POST', ['email' => 'test-admin@demo.com']);
$req->headers->set('Accept', 'application/json');
app()->validator->validate($req->all(), (new App\Http\Requests\LoginLookupRequest())->rules());
$res = (new App\Http\Controllers\Api\Auth\LoginLookupController())($req);
echo json_encode(json_decode($res->getContent()), JSON_PRETTY_PRINT) . "\n\n";

echo "2. GET /api/me (Testing User Payload and School Relation)\n";
$req = Illuminate\Http\Request::create('/api/me', 'GET');
$req->setUserResolver(function () use ($user) { return $user; });
$res = (new App\Http\Controllers\Api\UserController())($req);
echo json_encode(json_decode($res->getContent()), JSON_PRETTY_PRINT) . "\n\n";

echo "3. POST /api/me/profile (Testing Name/Profile Pictue Update logic)\n";
$req = Illuminate\Http\Request::create('/api/me/profile', 'POST', ['name' => 'Super Admin Updated']);
// mock request injection
app()->instance('request', $req);
$req->setUserResolver(function () use ($user) { return $user; });
$res = (new App\Http\Controllers\Api\UserProfileController())($req);
echo json_encode(json_decode($res->getContent()), JSON_PRETTY_PRINT) . "\n\n";

echo "4. GET /api/dashboard (Testing Dashboard Endpoint & Admin Data Widget calculation)\n";
$req = Illuminate\Http\Request::create('/api/dashboard', 'GET');
$req->setUserResolver(function () use ($user) { return $user; });
$res = (new App\Http\Controllers\Api\DashboardController())($req);
echo json_encode(json_decode($res->getContent()), JSON_PRETTY_PRINT) . "\n";

echo "\n--- API TEST END ---\n";
