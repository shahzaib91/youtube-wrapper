<h3>Background</h3>
<br/>
<p>This is just a "wrapper class" built on top of the "Google SDK" to minimize the effort of integrating the "Youtube" api into your projects.</p>
<h3>Pre Requisite</h3>
<ul>
    <li>Xampp/Wampp or Mamp server must be installed.</li>
    <li>Composer is installed and configured properly.</li>
    <li>Minimum PHP 7.2 (Not tested below or with later versions).</li>
    <li><a href="https://console.cloud.google.com/" target="_blank">Google Developer Console</a> account.</li>
</ul>
<h3>Setup Google Developer Console Project</h3>
<ul>
    <li>Sign in to <a href="https://console.cloud.google.com/" target="_blank">Google Developer Console</a>.</li>
    <li>Create "New Project" usually option is available between google cloud logo and the search bar.</li>
    <li>Enter "Project Name" and select "Organization" depending upon your account.</li>
    <li>Click blue "Create" button.</li>
    <li>Configure "0Auth consent screen" by filling asked details and click "Save and continue" button.</li>
    <li>
        Add the required Scopes you will be requesting from the user:<br/><br/>
        <code>
            https://www.googleapis.com/auth/youtube.readonly<br/>
            https://www.googleapis.com/auth/youtube.force-ssl
        </code>
        <br/><br/>
        click "Save and continue" after adding above scopes into consent screen.
        <br/><br/>
        <small>Note: If scopes are not available in your project click "Enable API & Services" button and enable "Youtube Data API" for your project.</small>
    </li>
    <li>Add "Test users" and click "Save and continue" button.</li>
    <li>Click "Credentials" option provided in left "Navigation Panel".</li>
    <li>Click "+ Create Credentials" button and choose "API Key" & copy it.</li>
    <li>Create "OAuth client ID" while you are on the "Credentials" page and configure "Redirect URIs".</li>
    <li>Click the little "Download Icon" under the heading "OAuth 2.0 Client IDs" and download config file.</li>
    <li>Make sure you have "API Key" and "Config JSON" file on this step.</li>
</ul>
<h3>Example Code</h3>
<a target="_blank" href="https://github.com/shahzaib91/youtube-wrapper-example">https://github.com/shahzaib91/youtube-wrapper-example</a>
<h3>Setup Library</h3>
<ul>
    <li>Run command <code>composer require mmg/youtube</code>.</li>
    <li>Add <code>require __DIR__.'/vendor/autoload.php';</code> on top of your php file.</li>
</ul>
<h3>Functions Description</h3>
<table style="width:100%" border="1" cellpadding="2" cellspacing="0">
    <thead>
        <tr>
            <th>Function Name</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <code>
                    public function prepare($name, $scopes, $configJsonPath, $apiKey)
                </code>
            </td>
            <td>
                Function to config client via wrapper class
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getAuthUrl()
                </code>
            </td>
            <td>
                Function to get auth url for authentication
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getAccessToken($code)
                </code>
            </td>
            <td>
                Function to fetch access token and set it to client
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function setAccessToken($accessTokenJson)
                </code>
            </td>
            <td>
                Function sets access token to google client object
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    private function parseAccessTokenJson($accessTokenJson)
                </code>
            </td>
            <td>
                Helper function to abstractedly perform json parsing
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getChannelsList()
                </code>
            </td>
            <td>
                Function to retrieve list of available channels owned by authenticated user
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getVideosList($channelId, $maxItems = 12)
                </code>
            </td>
            <td>
                Function to retrieve list of videos
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getVideoDetail($videoId)
                </code>
            </td>
            <td>
                Function to retrieve video details
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getVideoComments($videoId, $maxItems = 12)
                </code>
            </td>
            <td>
                Function to retrieve video comments and replies
            </td>
        </tr>
        <tr>
            <td>
                <code>
                    public function getVideoComments($videoId, $maxItems = 12)
                </code>
            </td>
            <td>
                Function is helping in tagging right reply to right comment by id
            </td>
        </tr>
    </tbody>
</table>