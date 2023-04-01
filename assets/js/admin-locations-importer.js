

(function( $ ) {
	'use strict';

    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_INPUT    = 1;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_HEADER   = 2;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_LOC_CD   = 3;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_DESC     = 4;
    const JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_TYPE     = 4;
    const JGBVWD_LOC_IMPRT_PRS_ERR_PARENT_NO_VLD  = 5;

    const JGBVWD_CLASS_NM_NOTICE_ERROR      = 'notice-error';
    const JGBVWD_CLASS_NM_NOTICE_SUCCESS    = 'notice-success';
    const JGBVWD_CLASS_NM_HIDDEN            = 'hidden';
    const JGBVWD_CLASS_NM_DISABLED          = 'disabled';

    class JGBVWDSLocationsImporter{
        #InElId;
        #elIdSndBtn;
        #elIdCbPrmTruncate;
        #elIdCbPrmCreateNew;
        #elIdCbPrmUpdExistent;
        #elIdResultsNotices;
        #elIdImportProgress;
        #input;
        #locations = [];
        #rowHeaders = [];
        #parseErrs = [];

        constructor(config){
            this.#InElId                = config.InputElementID;
            this.#elIdSndBtn            = config.elIdSndBtn;
            this.#elIdCbPrmTruncate     = config.elIdCbPrmTruncate;
            this.#elIdCbPrmCreateNew    = config.elIdCbPrmCreateNew;
            this.#elIdCbPrmUpdExistent  = config.elIdCbPrmUpdExistent;
            this.#elIdResultsNotices    = config.elIdResultsNotices;
            this.#elIdImportProgress    = config.elIdImportProgress;

            var that = this;
            
            $(this.#InElId).on('input propertychange paste',function(){
                that.readInput();
                that.parse();
                that.verifyErrorStatus();
                that.verifyLocationsCountStatus();
            });
    
            $(this.#elIdSndBtn).click( function(){
                that.setStatusSendingDataToSrvr();
                const dt = {
                    data: that.get_locations_data(),
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
                            that.setStatusAfterSentDataToSrvrSuccess();
                            location.reload();
                        } else {
                            console.log('No se pudieron actrualizar las locaciones.');
                        }
                        
                    },
                    complete: function( jqXHR, textStatus ){
                        that.setStatusAfterSentDataToSrvrCompleted();
                    }
                }
    
                $.ajax(ajxCfg);
            } );
        }

        setStatusSendingDataToSrvr(){
            if( $(this.#elIdImportProgress).hasClass(JGBVWD_CLASS_NM_HIDDEN) ){
                $(this.#elIdImportProgress).removeClass(JGBVWD_CLASS_NM_HIDDEN);
            }
        }

        setStatusAfterSentDataToSrvrCompleted(){
            if( !$(this.#elIdImportProgress).hasClass(JGBVWD_CLASS_NM_HIDDEN) ){
                $(this.#elIdImportProgress).addClass(JGBVWD_CLASS_NM_HIDDEN);
            }
        }

        setStatusAfterSentDataToSrvrSuccess(){
            this.resetErrorStatus();

            if( !$(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_NOTICE_SUCCESS) ){
                $(this.#elIdResultsNotices).addClass(JGBVWD_CLASS_NM_NOTICE_SUCCESS);
            }

            if( $(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_HIDDEN) ){
                $(this.#elIdResultsNotices).removeClass(JGBVWD_CLASS_NM_HIDDEN);
            }
        }

        resetErrorStatus(){
            $(this.#elIdResultsNotices).empty();
            if( !$(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_HIDDEN) ){
                $(this.#elIdResultsNotices).addClass(JGBVWD_CLASS_NM_HIDDEN);
            }

            if( $(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_NOTICE_ERROR) ){
                $(this.#elIdResultsNotices).removeClass(JGBVWD_CLASS_NM_NOTICE_ERROR);
            }

            if( $(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_NOTICE_SUCCESS) ){
                $(this.#elIdResultsNotices).removeClass(JGBVWD_CLASS_NM_NOTICE_SUCCESS);
            }

            if( !$(this.#elIdSndBtn).hasClass(JGBVWD_CLASS_NM_DISABLED) ){
                $(this.#elIdSndBtn).addClass(JGBVWD_CLASS_NM_DISABLED);
            }
        }

        verifyErrorStatus(){
            if(  this.#parseErrs.length > 0 ){
                this.resetErrorStatus();
                let i;
                let errInfoHtml = '<ul class="error-items">';

                for( i = 0; i < this.#parseErrs.length; i++ ){
                    errInfoHtml += '<li class="error-item">';
                    errInfoHtml += this.#parseErrs[i].msg;
                    errInfoHtml += '</li>';
                }

                errInfoHtml += '</ul>';

                $(this.#elIdResultsNotices).html(errInfoHtml);

                if( !$(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_NOTICE_ERROR) ){
                    $(this.#elIdResultsNotices).addClass(JGBVWD_CLASS_NM_NOTICE_ERROR);
                }

                if( $(this.#elIdResultsNotices).hasClass(JGBVWD_CLASS_NM_HIDDEN) ){
                    $(this.#elIdResultsNotices).removeClass(JGBVWD_CLASS_NM_HIDDEN);
                }

                if( $(this.#elIdSndBtn).hasClass(JGBVWD_CLASS_NM_DISABLED) ){
                    $(this.#elIdSndBtn).removeClass(JGBVWD_CLASS_NM_DISABLED);
                }
            }
        }

        verifyLocationsCountStatus(){
            if( this.#locations < 1 ){
                if( !$(this.#elIdSndBtn).hasClass(JGBVWD_CLASS_NM_DISABLED) ){
                    $(this.#elIdSndBtn).addClass(JGBVWD_CLASS_NM_DISABLED);
                }
            }
        }

        readInput(){
            this.#input = $(this.#InElId).val();
        }

        parse(){
            this.#parseErrs = [];
            this.parse_headers();
            this.parse_locations();
            this.validate_parents();
            
        }

        parse_locations(){
            this.#locations = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                let j = 0;
                let currentLocationParsing = {};
                let curColNm;
                let curColVl;
                for( j=1;j<lines.length;j++){
                    const fl = lines[j];
                    const mhs = fl.split('\t');
                    
                    currentLocationParsing = {};
                    for( i = 0; i < mhs.length; i++ ){
                        curColNm = this.#rowHeaders[i].trim();
                        curColVl = mhs[i].trim();
                        
                        if(    curColNm == 'location_code'
                            && curColVl == ''
                        ){
                            this.#parseErrs.push({
                                code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_LOC_CD,
                                msg: "Código de locación no válido en línea " + j
                            });
                        }

                        if(    curColNm == 'desc'
                            && curColVl == ''
                        ){
                            this.#parseErrs.push({
                                code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_DESC,
                                msg: "Descripción de locación no válida en línea " + j
                            });
                        }

                        if(    curColNm == 'type'
                            && curColVl == ''
                        ){
                            this.#parseErrs.push({
                                code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_TYPE,
                                msg: "Tipo de locación no válido en línea " + j
                            });
                        }

                        if(    curColNm == 'parent'
                            && curColVl == ''
                        ){
                            curColVl = null;
                        }

                        currentLocationParsing[ curColNm ] = curColVl;
                    }
                    
                    this.#locations.push(currentLocationParsing);
                }
                
            } else {
                this.#parseErrs.push({
                    code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_INPUT,
                    msg: "Entrada de datos inválida"
                });
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
                    if( mhs[i].trim() == '' ){
                        this.#parseErrs.push({
                            code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_HEADER,
                            msg: "Descripción de columna " + (i+1) + " de la cabecera no válida"
                        });
                    }
                    this.#rowHeaders.push( mhs[i] );
                }
                    
                
            } else {
                this.#parseErrs.push({
                    code: JGBVWD_LOC_IMPRT_PRS_ERR_EMPTY_INPUT,
                    msg: "Entrada de datos inválida"
                });
            }
        }

        validate_parents(){
            let i,j;
            let parentToCheck;
            let parentMatch;
            let parentsErrs = [];
            for( i = 0; i < this.#locations.length; i++){
                if( this.#locations.parent != null ){
                    parentToCheck = this.#locations.parent;
                    parentMatch = false;
                    for( j = 0; j < this.#locations.length; j++ ){
                        if( this.#locations.parent == parentToCheck ){
                            parentMatch = true;
                            break;
                        }
                    }
                    if( !parentMatch ){
                        err = {
                            code: JGBVWD_LOC_IMPRT_PRS_ERR_PARENT_NO_VLD,
                            msg: "Código de locación superior inexistentea en locación de línea " + i
                        };
                        parentsErrs.push(err);
                        this.#parseErrs.push(err);
                    }
                }
            }
            return parentsErrs;
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
            elIdCbPrmUpdExistent: '#locations-importer-form #locations-update',
            elIdResultsNotices: '#locations-importer-form #result-notice',
            elIdImportProgress: '#locations-importer-form .uploading img'
        }

        const ourIprtr = new JGBVWDSLocationsImporter( importerCfg );

    });

})(jQuery);