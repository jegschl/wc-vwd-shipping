

(function( $ ) {
	'use strict';

    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_HEADER   = 1;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_LOC_CD   = 2;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_DESC     = 3;
    const JGBVWD_LOC_IMPRT_PRS_ERR_PARENT_NO_VLD  = 4;

    class JGBVWDSLocationsImporter{
        #InElId;
        #elIdSndBtn;
        #SndBtnDisabledClassNm = 'disabled';
        #elIdCbPrmTruncate;
        #elIdCbPrmCreateNew;
        #elIdCbPrmUpdExistent;
        #input;
        #locations = [];
        #rowHeaders = [];

        constructor(config){
            this.#InElId = config.InputElementID;
            this.#elIdSndBtn = config.elIdSndBtn;
            this.#elIdCbPrmTruncate = config.elIdCbPrmTruncate;
            this.#elIdCbPrmCreateNew = config.elIdCbPrmCreateNew;
            this.#elIdCbPrmUpdExistent = config.elIdCbPrmUpdExistent;
            if( config.SndBtnDisabledClassNm !== undefined ){
                this.#SndBtnDisabledClassNm = config.SndBtnDisabledClassNm;
            }
            var that = this;
            
            $(this.#InElId).on('input propertychange paste',function(){
                debugger;
                that.readInput();
                that.parse();
            });
    
            $(this.#elIdSndBtn).click( function(){
                const dt = {
                    data: this.get_locations_data(),
                    createNewLocations: that.checkParameterCreateNewLocations(),
                    updateExistentLocations: that.checkParameterUpdateExistentLocations(),
                    truncateLocations: that.checkParameterTruncateLocations()
                };
    
                const ajxCfg = {
                    method: "POST",
                    url: JGB_VWDS.urlLocationsImprt,
                    contentType: "application/json; charset=UTF-8",
                    data: JSON.stringify(dt),
                    error: function(  jqXHR,  textStatus,  errorThrown){
                        console.log('No se pudieron actrualizar las locaciones.');
                    },
                    success: function( data,  textStatus,  jqXHR){
                    
                        if( data.err_status != undefined && data.err_status == 0){
                            location.reload();
                        } else {
                            console.log('No se pudieron actrualizar las locaciones.');
                        }
                        
                    },
                    complete: function( jqXHR, textStatus ){
    
                    }
                }
    
                $.ajax(ajxCfg);
            } );
        }

        readInput(){
            this.#input = $(this.#InElId).val();
        }

        parse(){
            this.parse_headers();
            this.parse_locations();
        }

        parse_locations(){
            this.#locations = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                let j = 0;
                let currentLocationParsing = {};
                for( j=1;j<lines.length;j++){
                    const fl = lines[j];
                    const mhs = fl.split('\t');
                    
                    currentLocationParsing = {};
                    for( i = 0; i < mhs.length; i++ ){
                        currentLocationParsing[ this.#rowHeaders[i] ] = mhs[i];
                    }
                    
                    this.#locations.push(currentLocationParsing);
                }
                
            }
        }


        parse_headers(){
            this.#rowHeaders = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                
                const fl = lines[0];
                const mhs = fl.split('\t');
                
                
                for( i = 0; i < mhs.length; i++ ){
                    this.#rowHeaders.push( mhs[i] );
                }
                    
                
            }
        }

        get_locations_data(){
            return this.#locations;
        }

        is_valid_data(){
            if( this.#input!= undefined && this.#input!='' ){
                return false;
            }

            return true;
        }

        checkParameterTruncateLocations(){
            if( $(this.#elIdCbPrmTruncate).is(":checked") ){
                return true;
            }
            return false;
        }

        checkParameterCreateNewLocations(){
            if( $(this.#elIdCbPrmCreateNew).is(":checked") ){
                return true;
            }
            return false;
        }

        checkParameterUpdateExistentLocations(){
            if( $(this.#elIdCbPrmUpdExistent).is(":checked") ){
                return true;
            }
            return false;
        }
    }

    $(document).ready(function () {

        const importerCfg = {
            InputElementID: '#input-locations-import-data',
            elIdSndBtn: '#locations-importer-form .button.save',
            elIdCbPrmTruncate: '#locations-importer-form #locations-truncate',
            elIdCbPrmCreateNew: '#locations-importer-form #locations-create-new',
            elIdCbPrmUpdExistent: '#locations-importer-form #locations-update'
        }

        const ourIprtr = new JGBVWDSLocationsImporter( importerCfg );

    });

})(jQuery);