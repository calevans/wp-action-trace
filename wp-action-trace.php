<?php
/*
Plugin Name: Wp-action-trace
Version: 0.2.3
Description: A simple plugin to show you exactly what actions are being called when you run WordPress.
Author: Cal Evans
Author URI: http://blog.calevans.com
Plugin URI: http://blog.calevans.com
Text Domain: wp-action-trace
Domain Path:http://blog.calevans.com
*/

function calevans_action_trace()
{
    /*
     * Even though this plugin should never EVER be used in production, this is 
     * a safety net. You have to actually set the showTrace=1 flag in the query 
     * string for it to operate. If you don't it will still slow down your 
     * site, but it won't do anything.
     */
	if (!isset($_GET['showDebugTrace']) || (bool)$_GET['showDebugTrace']!==true) {
		return;
	}

    /*
     * There are 2 other flags you can set to control what is output
     */
    $showArgs = (isset($_GET['showDebugArgs'])?(bool)$_GET['showDebugArgs']:false);
    $showTime = (isset($_GET['showDebugTime'])?(bool)$_GET['showDebugTime']:false);


    /*
     * This is the main array we are using to hold the list of actions
     */
	static $actions = [];



    /*
     * Some actions are not going to be of interet to you. Add them into this 
     * array to exclude them. Remove the two default if you want to see them.
     */
    $excludeActions = ['gettext','gettext_with_context'];
    $thisAction     = current_filter();
    $thisArguments  = func_get_args();

    if (!in_array( $thisAction, $excludeActions )) {
        $actions[] = ['action'    => $thisAction,
		              'time'      => microtime(true),
		              'arguments' => print_r($thisArguments,true)];
    }


    /*
     * Shutdown is the last action, process the list.
     */ 
    if ($thisAction==='shutdown') {
        calevans_format_debug_output($actions,$showArgs,$showTime);
    }

	return;
}


function calevans_format_debug_output($actions=[],$showArgs=false, $showTime=false)
{
   /*
     * Let's do a little formatting here.
     * The class "debug" is so you can control the look and feel
     */
    echo '<pre class="debug">';

    foreach($actions as $thisAction) {
        echo "Action Name : ";

        /*
         * if you want the timings, let's make sure everything is padded out properly.
         */
        if ($showTime) {
            $timeParts = explode('.',$thisAction['time']);
            echo '(' . $timeParts[0] . '.' .  str_pad($timeParts[1],4,'0') . ') ';
        }


        echo $thisAction['action'] . PHP_EOL;

        /*
         * If you've requested the arguments, let's display them.
         */
        if ($showArgs && count($thisAction['arguments'])>0) {
            echo "Args:" . PHP_EOL . print_r($thisAction['arguments'],true);
            echo PHP_EOL;
        }
    }

    echo '</pre>';
	
	return;
}

/*
 * Hook it into WordPress.
 * all = add this to every action. 
 * calevans_action_trace = the name of the function above to call
 * 99999 = the priority. This is the lowest priority action in the list.
 * 99 = the number of parameters that this method can accept. Honestly, if you have a action approaching this many parameter, you really are doing sometheing wrong. 
 * 
 */
add_action( 'all', 'calevans_action_trace', 99999, 99 );
