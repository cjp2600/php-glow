# GLOW CLIENT

Simple http request Orchestrate for RoadRunner.

### Roadrunner service [roadrunner-glow](https://github.com/cjp2600/roadrunner-glow)

## Example
```php
class GlowController
{
    public function test(): Response
    {
        // set glow configuration
        $config = new Configuration();

        // debug query information on rr output
        $config->setDebug(false);

        // init glow
        $glow = new Glow($config);

        // build sequences
        $glow->sequences(

            // first auth request
            ($glow->request('auth')
                ->setMethod('post')
                ->setUrl('https://api.example.com/api/v1/users/login')
                ->setData([
                    'email' => 'user@example.com',
                    'password' => 'PassWord',])
                // set $token variable use json path
                ->setVariable('token', '$.data.accessToken')),

            // parallel sequence wrapper
            // inside it, all requests will be executed in parallel
            $glow->parallel(
                // get current user from token and set $userId variable
                // default get method
                ($glow->request('current')
                    ->setAuth('$token') // use token variable form auth request
                    ->setUrl('https://api.example.com/api/v1/users/current')
                    ->setVariable('userId', '$.data.id')),

                // get user notices
                ($glow->request('count')
                    ->setAuth('$token')
                    ->setUrl('https://api.example.com/api/v1/notices/count'))
            )

        );

        // execute all jobs
        $response = $glow->execute();

        // get data from responses
        $user = $response->getResponse('current');
        $notices = $response->getResponse('count');

        return new Response(
            '<html><body> User: ' . $user->data->firstName . '  <br>  Notices count: ' . $notices->data->totalCount . ' </body></html>'
        );
    }
}
```