<header class="main">

    <script type="text/javascript">
        function preventBack() { window.history.forward(); }
        setTimeout("preventBack()", 0);
        window.onunload = function () { null };
    </script>

    <!-- <div class="container wide">
        <div class="content slim">
            <div class="set">
                <div class="fill">
                    <a class="pseudoshop" href="/">Payment for Quote <strong> <?php echo $quotenumber; ?></strong></a>
                </div>

                <div class="fit">
                <a class="braintree" href="https://bestaccessdoors.com" target="_blank">BestAccessDoors</a>
                </div>
            </div>
        </div>
    </div> -->

    <div class="notice-wrapper">
        <?php if(isset($_SESSION["errors"])) : ?>
            <div class="show notice error notice-error">
                <span class="notice-message">
                    <?php
                        echo($_SESSION["errors"]);
                        unset($_SESSION["errors"]);
                    ?>
                <span>
            </div>
        <?php endif; ?>
    </div>
</header>
