<script>

    <?php
        if (isset($access_token))
            echo "localStorage.setItem('access_token','$access_token');";

        if (isset($refresh_token))
            echo "localStorage.setItem('refresh_token','$refresh_token');";

        if (isset($expires_in))
            echo "localStorage.setItem('expires_in','$expires_in');";    
        
        if (isset($exp))
            echo "localStorage.setItem('exp', parseInt((new Date).getTime() / 1000) + $expires_in);"; 
           
          
        if (!isset($error)){
            echo "window.location.replace('/')";
        } 
              
    ?>

</script>    


<?php 
    if (isset($error)){
        echo '<div class="error">';
        echo $error;
        echo '</div>';
    }     
?>
