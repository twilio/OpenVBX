<!-- this page is not used anywhere? -->

In this example, we'll build the familiar Twilio Hello Monkey with a slight twist. We're going to build it so you can attach another Applet to it at the end of the &ldquo;Hello Monkey&rdquo; application.
To get started, create a directory for the name of your applet inside &lt;openvbx&gt;/applets.

        mkdir <openvbx>/plugins/<plugin>/applets/hello-monkey

Lets define something about this applet, like its name and who built it. You define this through a readme.json file. If you're familiar with json, then this should be a walk in the park, otherwise just copy and paste our example into your favorite text editor.

        mate <openvbx>/plugins/<plugin>/applets/hello-monkey/applet.json

### applet.json ###


        {
                "name" : "Hello Monkey",
                "description" : "My Favorite Monkey",
                "type" : "voice"
        }

Its time to create the voice applet, all you need to do is stub out two files that will be used to define your user interface and what twiml to display when someone calls your application.

        touch <openvbx>/plugins/<plugin>/applets/hello-monkey/ui.php
        touch <openvbx>/plugins/<plugin>/applets/hello-monkey/twiml.php

Within the User Interface of your Applet, people will see the html you write. The &ldquo;Hello Monkey&rdquo; Applet tells the user with the text to speech engine, so we will describe that to the user. Then we will add a DropZone so the user can add another applet at the end of the application.

        mate <openvbx>/plugins/<plugin>/applets/hello-monkey/ui.php

        <h2>This applet tells the user &ldquo;Hello Monkey&rdquo; at this point during the call.</h2>
        <?php echo AppletUI::DropZone('next'); ?>


Inside your TwiML, you can add anything you want to display to Twilio. For this example, we will say &ldquo;Hello Monkey&rdquo; and then redirect the caller if they have defined the &lsquo;next&rsquo; DropZone.

        mate <openvbx>/plugins/<plugin>/applets/hello-monkey/twiml.php

        <?php
        header("content-type: text/xml");
        $next = AppletInstance::getDropZoneUrl('next');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        ?>
        <Response>
                 <Say>Hello Monkey</Say>
                 <?php if(!empty($next)): ?>
                 <Redirect><?php echo $next; ?></Redirect>
                 <?php endif; ?>
        </Response>

Thats it! The application will tell the caller &ldquo;Hello Monkey&rdquo; and redirect the caller if they dropped a new Applet in the 'next' field. We used the method getDropZoneUrl to get the path to the next applet and put it inside a &lt;Redirect /&gt; tag.


