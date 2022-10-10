<p class="alert alert-danger fs-13">{!! '<strong>Important:</strong> As per <a href="'. the_link('codecanyon.net/licenses/standard'). '" target="_blank">'. ucfirst('env') .'ato License</a> terms, one '.'purchase code'.' is valid to use one application. '.'Please activate'.' the application into correct domain '. '/' .' path to '.'avoid any kind'.' of issues'.'.' !!}</p>
<p>{!! '<strong>Caution:</strong> '.'The following data will be send to ' . '<strong>Sof' . 'tnio</strong>' . ' server to validate your '. 'purchase information' .''.'.' !!}</p>
<div class="card card-bordered mb-4">
    <table class="table fs-12px bg-lighter">
        <tr>
            <td width="120">{!! 'Registration Info:' !!}</td>
            <td>{!! 'Purchase' . ' Code' . ', <br>' . 'Username & Email' !!}</td>
        </tr>
        <tr>
            <td>{!! 'Site' . '/' . 'App Name:' !!}</td>
            <td><span class="text-wrap wide-120px">{{ base64_encode(site_info('name')) }}</span></td>
        </tr>
        <tr>
            <td>{!! 'Site' . '/' . 'App URL:' !!} </td>
            <td>
                <span class="text-wrap wide-120px">{{ str_replace(['https://', 'http://'], '', site_info('url')) }}</span>
            </td>
        </tr>
        <tr>
            <td>{!! 'Installed' . ' ' . 'Version:' !!}</td>
            <td>{{ 'v'.config('app'.'.'.'version') }}</td>
        </tr>
    </table>
</div>
<p class="alert alert-warning fs-13px font-italic">{!! '<strong>Please Note:</strong> <br>'.'We will never collect any ' . 'confidential' . ' data such as '. 'transactions' .', emails or usernames.' !!}</p>