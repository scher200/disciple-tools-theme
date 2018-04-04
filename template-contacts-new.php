<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( esc_html__( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "contacts/", __( "Contacts" ) ],
    ],
    __( "New contact" )
);

( function() {

?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box">
                <label>
                    <?php esc_html_e( "Name of contact", "disciple_tools" ); ?>
                    <input name="title" type="text" placeholder="<?php esc_html_e( "Name", "disciple_tools" ); ?>" required aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <label>
                    <?php esc_html_e( "Phone number", "disciple_tools" ); ?>
                    <input name="phone" type="text" type="tel" placeholder="<?php esc_html_e( "Phone number", "disciple_tools" ); ?>">
                </label>

                <label>
                    <?php esc_html_e( "Source", "disciple_tools" ); ?>
                    <select name="sources" required aria-describedby="source-help-text">
                        <?php foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $source_key => $source ): ?>
                            <option value="<?php echo esc_attr( $source_key, 'disciple_tools' ); ?>">
                                <?php echo esc_html( $source['label'] )?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <p class="help-text" id="source-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <div class="locations">
                    <var id="locations-result-container" class="result-container"></var>
                    <div id="locations_t" name="form-locations" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-locations"
                                               name="locations[query]" placeholder="<?php esc_html_e( "Search Locations", 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>
                <label>
                    <?php esc_html_e( "Initial comment", "disciple_tools" ); ?>
                    <textarea name="initial_comment" placeholder="<?php esc_html_e( "Initial comment", "disciple_tools" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <button class="button loader js-create-contact-button" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                </div>
            </div>

        </div>

        <div class="large-2 medium-12 small-12 cell"></div>
    </div>
</div>

<script>jQuery(function($) {
    $(".js-create-contact-button").removeAttr("disabled");
    let selectedLocations = []
    $(".js-create-contact").on("submit", function() {
        $(".js-create-contact-button")
            .attr("disabled", true)
            .addClass("loading");
        let source = $(".js-create-contact select[name=sources]").val()
        API.create_contact({
            title: $(".js-create-contact input[name=title]").val(),
            contact_phone: [{value:$(".js-create-contact input[name=phone]").val()}],
            sources: {values:[{value:source}]},
            locations: {values:selectedLocations.map(i=>{return {value:i}})},
            initial_comment: $(".js-create-contact textarea[name=initial_comment]").val(),
        }).then(function(data) {
            window.location = data.permalink;
        }).catch(function(error) {
            $(".js-create-contact-button").removeClass("loading").addClass("alert");
            $(".js-create-contact").append(
                $("<div>").html(error.responseText)
            );
            console.error(error);
        });
        return false;
    });
        /**
         * Locations
         */
        $.typeahead({
            input: '.js-typeahead-locations',
            minLength: 0,
            searchOnFocus: true,
            maxItem: 20,
            template: function (query, item) {
                return `<span>${_.escape(item.name)}</span>`
            },
            source: TYPEAHEADS.typeaheadSource('locations', 'dt/v1/locations-compact/'),
            display: "name",
            templateValue: "{{name}}",
            dynamic: true,
            multiselect: {
                matchOn: ["ID"],
                callback: {
                    onCancel: function (node, item) {
                        $('#share-result-container').html("");
                        _.pull(selectedLocations, item.ID)
                    }
                },
            },
            callback: {
                onClick: function(node, a, item, event){
                    selectedLocations.push(item.ID)
                },
                onResult: function (node, query, result, resultCount) {
                    let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                    $('#locations-result-container').html(text);
                },
                onHideLayout: function () {
                    $('#locations-result-container').html("");
                },
            }
        });
});</script>


<?php

} )();

get_footer();
