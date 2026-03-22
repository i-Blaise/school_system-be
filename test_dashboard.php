$school = App\Models\School::firstOrCreate(['slug' => 'demo'], ['name' => 'Demo']);
$user = App\Models\User::firstOrCreate(
    ['email' => 'admin@demo.com'],
    [
        'school_id' => $school->id,
        'name' => 'Admin',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]
);

$request = Illuminate\Http\Request::create('/api/dashboard', 'GET');
$request->setUserResolver(function () use ($user) { return $user; });
$controller = new App\Http\Controllers\Api\DashboardController();
$response = $controller($request);

echo $response->getContent();
