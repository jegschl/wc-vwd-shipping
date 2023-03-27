

(function( $ ) {
	'use strict';

    const IPRTR_COL_ROW_MODE_FRW_FCZ = 1;
    const IPRTR_COL_ROW_MODE_FRZ_FCW = 0;

    const IPRTR_WR_MODE_SUPLIM = 1;
    const IPRTR_WR_MODE_DOWSUP = 0;

    class JGBVWDSImporter{
        #InElId;
        #input;
        #headersMode;
        #weightMode;
        #weights = [];
        #zones = [];
        #prices = [];

        constructor(InputElementID){
            this.#InElId = InputElementID;
           
        }

        readInput(){
            this.#input = $(this.#InElId).val();
        }

        setHeadersMode(mode){
            this.#headersMode = mode;
        }

        setWeightMode(mode){
            this.#weightMode = mode;
        }

        parse(){
            this.parse_weights();
            this.parse_zones();
            this.parse_prices();
        }

        parse_prices(){
            this.#prices = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                let j = 0;
                let cells;
                let plbwr;
                let currentRawPrice;
                
                for( i=1; i<lines.length; i++){
                    cells = lines[i].split('\t');
                    plbwr = [];
                    for( j=1; j<cells.length; j++){
                        //debugger;
                        currentRawPrice = cells[j];
                        currentRawPrice = currentRawPrice.replace(/[$,.\s]+/g,"");
                        plbwr.push(parseInt(currentRawPrice));
                    }
                    this.#prices.push(plbwr);
                }

                if( this.#headersMode == IPRTR_COL_ROW_MODE_FRW_FCZ ){
                    // se invierte la matriz this.#prices
                    this.#prices = math.inv( this.#prices );
                }
            }
        }

        parse_weights(){
            this.#weights = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                if( this.#headersMode == IPRTR_COL_ROW_MODE_FRW_FCZ ){
                    const fl = lines[0];
                    const mhs = fl.split('\t');
                   
                    
                    for( i = 1; i < mhs.length; i++ ){
                        if( mhs[i] != '' ){
                            this.#weights.push( mhs[i] );
                        }
                    }
                    
                } else {
                    let cl;
                    let cRange, j, previowsSupLimRange;
                    let limS, limI;
                    previowsSupLimRange = '';
                    j = 0;
                    for( i = 1; i < lines.length; i++ ){
                        cl = lines[i].split('\t');
                        
                        if( cl[0] != '' ){
                            
                            
                            cRange = [];
                            if( this.#weightMode == IPRTR_WR_MODE_SUPLIM ){
                                
                                if(i == 1){
                                    cRange[0] = 0;
                                } else {
                                    previowsSupLimRange = this.#weights[j-1][1]
                                    cRange[0] = previowsSupLimRange + 0.001;
                                }
                                cRange[1] = parseFloat(cl[0]);
                            } else {
                                limI = parseFloat(cl[0].split('-')[0]);
                                limS = parseFloat(cl[0].split('-')[1]);
                                cRange[0] = limI;
                                cRange[1] = limS;
                            }
                            this.#weights.push( cRange );
                            j++;
                        }
                    }
                }
            }
            
            
        }

        parse_zones(){
            this.#zones = [];
            if( this.#input!= undefined && this.#input!='' ){
                const lines = this.#input.split('\n');
                let i = 0;
                if( this.#headersMode == IPRTR_COL_ROW_MODE_FRZ_FCW ){
                    const fl = lines[0];
                    const mhs = fl.split('\t');
                    
                    
                    for( i = 1; i < mhs.length; i++ ){
                        this.#zones.push( mhs[i] );
                    }
                    
                } else {
                    let cl;

                    for( i = 1; i < lines.length; i++ ){
                        cl = lines[i].split('\t');
                        this.#zones.push( cl[0] );
                    }
                }
            }
        }

        get_zones_by_wr(){
            return {
                zones: this.#zones,
                weight: this.#weights,
                prices: this.#prices
            };
        }
    }

    const ourIprtr = new JGBVWDSImporter( '#input-import-data' );

    function read_params(){
        ourIprtr.setHeadersMode( parseInt( $('input[name="rbg-col-row-mode"]:checked').val() ) );
        ourIprtr.setWeightMode( parseInt( $('input[name="rbg-weight-range-mode"]:checked').val() ))
        ourIprtr.readInput();
    }

    $(document).ready(function () {

        $('#input-import-data').on('input propertychange paste',function(){
            read_params();
            
            ourIprtr.parse();
        });

        $('.button.import-zone-prices').click( function(){
            const dt = ourIprtr.get_zones_by_wr();

            const ajxCfg = {
                method: "POST",
                url: JGB_VWDS.urlSetZoneByWR,
                contentType: "application/json; charset=UTF-8",
                data: JSON.stringify(dt),
                error: function(  jqXHR,  textStatus,  errorThrown){
                    console.log('No se pudieron actrualizar las zonas.');
                },
                success: function( data,  textStatus,  jqXHR){
                
                    if( data.err_status != undefined && data.err_status == 0){
                        location.reload();
                    } else {
                        console.log('No se pudieron actrualizar la zonas.');
                    }
                    
                }
            }

            $.ajax(ajxCfg);
        } );

    });

})(jQuery);