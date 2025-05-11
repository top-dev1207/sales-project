const config = {
  currencySymbol: '$ ',
  decimalCharacter: ',',
  digitGroupSeparator: '.',
  decimalCharacterAlternative: '.',
  unformatOnSubmit: true,
  outputFormat: 'number',
  minimumValue: '-999999999',
  maximumValue: '999999999',
};

const configNegative = {
  currencySymbol: '$ ',
  decimalCharacter: ',',
  digitGroupSeparator: '.',
  decimalCharacterAlternative: '.',
  unformatOnSubmit: true,
  outputFormat: 'number',
  minimumValue: '-999999999',
  maximumValue: '999999999',
  styleRules: AutoNumeric.options.styleRules.positiveNegative
};

//new AutoNumeric(domElement, { styleRules: AutoNumeric.options.styleRules.positiveNegative });

const totalf = new AutoNumeric('#totalf', config);
const totalIva = new AutoNumeric('#totalIva', config);
const pagado = new AutoNumeric('#pagado', config);
const saldo = new AutoNumeric('#saldo', config);
const importeBruto1 = new AutoNumeric('#importeBruto1', config);
const importeBruto2 = new AutoNumeric('#importeBruto2', config);
const importeBruto3 = new AutoNumeric('#importeBruto3', config);
const importeBruto4 = new AutoNumeric('#importeBruto4', config);
const totalIva1 = new AutoNumeric('#totalIva1', config);
const totalIva2 = new AutoNumeric('#totalIva2', config);
const totalIva3 = new AutoNumeric('#totalIva3', config);
const totalIva4 = new AutoNumeric('#totalIva4', config);
const subtotal1 = new AutoNumeric('#subtotal1', config);
const subtotal2 = new AutoNumeric('#subtotal2', config);
const subtotal3 = new AutoNumeric('#subtotal3', config);
const subtotal4 = new AutoNumeric('#subtotal4', config);
const impuestoGanancia = new AutoNumeric('#impuestoGanancia', config);
const impuestoInterno = new AutoNumeric('#impuestoInterno', config);
const impuestos = new AutoNumeric('#impuestos', config);
const redondeo = new AutoNumeric('#redondeo', config);
const total = new AutoNumeric('#total', config);
const pago1 = new AutoNumeric('#pago1', config);
const pago2 = new AutoNumeric('#pago2', config);
const pago3 = new AutoNumeric('#pago3', config);
const pago4 = new AutoNumeric('#pago4', config);


$('#importeBruto1').on('change',  function() {calcularSubtotales(1, "bruto")});
$('#importeBruto2').on('change',  function() {calcularSubtotales(2, "bruto")});
$('#importeBruto3').on('change',  function() {calcularSubtotales(3, "bruto")});
$('#importeBruto4').on('change',  function() {calcularSubtotales(4, "bruto")});

$('#iva1').on('change',           function() {calcularSubtotales(1, "ivaPorcentaje")});
$('#iva2').on('change',           function() {calcularSubtotales(2, "ivaPorcentaje")});
$('#iva3').on('change',           function() {calcularSubtotales(3, "ivaPorcentaje")});
$('#iva4').on('change',           function() {calcularSubtotales(4, "ivaPorcentaje")});

$('#totalIva1').on('change',      function() {calcularSubtotales(1, "totalIva")});
$('#totalIva2').on('change',      function() {calcularSubtotales(2, "totalIva")});
$('#totalIva3').on('change',      function() {calcularSubtotales(3, "totalIva")});
$('#totalIva4').on('change',      function() {calcularSubtotales(4, "totalIva")});

$('#subtotal1').on('change',      function() {calcular_brutos_completos(1)});
$('#subtotal2').on('change',      function() {calcular_brutos_completos(2)});
$('#subtotal3').on('change',      function() {calcular_brutos_completos(3)});
$('#subtotal4').on('change',      function() {calcular_brutos_completos(4)});

$('#redondeo').on('change',         function() {calcular_total()});
$('#impuestos').on('change',        function() {calcular_total()});
$('#impuestoGanancia').on('change', function() {calcular_total()});
$('#impuestoInterno').on('change',  function() {calcular_total()});

$('#total').on('change',        function() {calcular_brutos_completos(1)});


function calcular_total(){
    console.log('calcular_total()');
    let subt1 = Number.parseFloat(subtotal1.getNumericString());
    let subt2 = Number.parseFloat(subtotal2.getNumericString());
    let subt3 = Number.parseFloat(subtotal3.getNumericString());
    let subt4 = Number.parseFloat(subtotal4.getNumericString());
    
    
    if(isNaN(subt1)) subt1=0;
    if(isNaN(subt2)) subt2=0;
    if(isNaN(subt3)) subt3=0;
    if(isNaN(subt4)) subt4=0;
    
    let tiva1 = Number.parseFloat(totalIva1.getNumericString());
    let tiva2 = Number.parseFloat(totalIva2.getNumericString());
    let tiva3 = Number.parseFloat(totalIva3.getNumericString());
    let tiva4 = Number.parseFloat(totalIva4.getNumericString());

    if(isNaN(tiva1)) tiva1=0;
    if(isNaN(tiva2)) tiva2=0;
    if(isNaN(tiva3)) tiva3=0;
    if(isNaN(tiva4)) tiva4=0;

    //console.log("Subt1: "+subt1);
    //console.log("Subt2: "+subt2);
    //console.log("Subt3: "+subt3);
    //console.log("Subt4: "+subt4);

    let totalInicial = subt1+subt2+subt3+subt4;
    let sumTotalIva = tiva1+tiva2+tiva3+tiva4;

    //console.log("total Iva: "+sumTotalIva);

    let rd =  Number.parseFloat(redondeo.getNumericString());
    let imp =  Number.parseFloat(impuestos.getNumericString());
    let img =  Number.parseFloat(impuestoGanancia.getNumericString());
    let imit =  Number.parseFloat(impuestoInterno.getNumericString());
    
    let cuentaTotal =     Number.parseFloat(totalInicial + rd + imp + img + imit);
    //let cuentaTotal =     Number.parseFloat(totalInicial + redondeo + impuestos + impGanancia + impInterno).toFixed(2);

    total.set(cuentaTotal);
    totalf.set(cuentaTotal);
    totalIva.set(sumTotalIva);

    CalculoDePagos();
  }    



function calcular_brutos_completos(nro){
    console.log('calcular_brutos_completos()');

    switch(nro) {
      case 1:
        subtotal  = Number.parseFloat(subtotal1.getNumericString());
        iva       = Number.parseFloat($('#iva1').val());
        bruto     = subtotal/(1+iva/100);
        totaliva  = bruto*iva/100;
        importeBruto1.set(bruto);
        totalIva1.set(totaliva);
        break;

      case 2:
        subtotal  = Number.parseFloat(subtotal2.getNumericString());
        iva       = Number.parseFloat($('#iva2').val());
        bruto     = subtotal/(1+iva/100);
        totaliva  = bruto*iva/100;
        importeBruto2.set(bruto);
        totalIva2.set(totaliva);
        break;

      case 3:
        subtotal  = Number.parseFloat(subtotal3.getNumericString());
        iva       = Number.parseFloat($('#iva3').val());
        bruto     = subtotal/(1+iva/100);
        totaliva  = bruto*iva/100;
        importeBruto3.set(bruto);
        totalIva3.set(totaliva);
        break;

      case 4:
        subtotal  = Number.parseFloat(subtotal4.getNumericString());
        iva       = Number.parseFloat($('#iva4').val());
        bruto     = subtotal/(1+iva/100);
        totaliva  = bruto*iva/100;
        importeBruto4.set(bruto);
        totalIva4.set(totaliva);  
        break;

    }
    //calcularSubtotales(1);
}   

function calcularSubtotales(nro, origen){
  //let t  = $('#total').val();

  console.log("calcularSubtotales "+nro+" "+origen);
  
  let sbtot1=0;
  let sbtot2=0;
  let sbtot3=0;
  let sbtot4=0;


  switch(origen){
    case "bruto":
        //leo %IVA y calculo subtotal e IVAtotal
        iva1 = Number.parseFloat(document.getElementById("iva1").value);
        iva2 = Number.parseFloat(document.getElementById("iva2").value);
        iva3 = Number.parseFloat(document.getElementById("iva3").value);
        iva4 = Number.parseFloat(document.getElementById("iva4").value);
        
        if(isNaN(iva1)) iva1=0;
        if(isNaN(iva2)) iva2=0;
        if(isNaN(iva3)) iva3=0;
        if(isNaN(iva4)) iva4=0;

        ib1 = Number.parseFloat(importeBruto1.getNumericString());
        ib2 = Number.parseFloat(importeBruto2.getNumericString());
        ib3 = Number.parseFloat(importeBruto3.getNumericString());
        ib4 = Number.parseFloat(importeBruto4.getNumericString());
        
        if(isNaN(ib1)) ib1=0;
        if(isNaN(ib2)) ib2=0;
        if(isNaN(ib3)) ib3=0;
        if(isNaN(ib4)) ib4=0;

      break;


    case "ivaPorcentaje":
        //leo bruto y calculo Subtotal e IVAtotal
        ib1 = Number.parseFloat(importeBruto1.getNumericString());
        ib2 = Number.parseFloat(importeBruto2.getNumericString());
        ib3 = Number.parseFloat(importeBruto3.getNumericString());
        ib4 = Number.parseFloat(importeBruto4.getNumericString());

        if(isNaN(ib1)) ib1=0;
        if(isNaN(ib2)) ib2=0;
        if(isNaN(ib3)) ib3=0;
        if(isNaN(ib4)) ib4=0;

        iva1 = Number.parseFloat(document.getElementById("iva1").value);
        iva2 = Number.parseFloat(document.getElementById("iva2").value);
        iva3 = Number.parseFloat(document.getElementById("iva3").value);
        iva4 = Number.parseFloat(document.getElementById("iva4").value);
        
        if(isNaN(iva1)) iva1=0;
        if(isNaN(iva2)) iva2=0;
        if(isNaN(iva3)) iva3=0;
        if(isNaN(iva4)) iva4=0;

      break;
    case "totalIva":
        //leo subtotal y calculo bruto e %Iva
        st1 = Number.parseFloat(subtotal1.getNumericString());
        st2 = Number.parseFloat(subtotal2.getNumericString());
        st3 = Number.parseFloat(subtotal3.getNumericString());
        st4 = Number.parseFloat(subtotal4.getNumericString());

        if(isNaN(st1)) st1=0;
        if(isNaN(st2)) st2=0;
        if(isNaN(st3)) st3=0;
        if(isNaN(st4)) st4=0;

        ivatotal1 = Number.parseFloat(totalIva1.getNumericString());
        ivatotal2 = Number.parseFloat(totalIva2.getNumericString());
        ivatotal3 = Number.parseFloat(totalIva3.getNumericString());
        ivatotal4 = Number.parseFloat(totalIva4.getNumericString());
        
        if(isNaN(ivatotal1)) ivatotal1=0;
        if(isNaN(ivatotal2)) ivatotal2=0;
        if(isNaN(ivatotal3)) ivatotal3=0;
        if(isNaN(ivatotal4)) ivatotal4=0;
      break;
  }


  elib1  = document.getElementById("contenido_check_ImporteBruto_1");
  elib1a = document.getElementById("contenido_check_ImporteBruto_1a");
  //ibel1A = document.getElementById("contenido_check_ImporteBruto_1a1");
  chib1  = document.getElementById("check_ImporteBruto1");
  
  elib2  = document.getElementById("contenido_check_ImporteBruto_2");
  elib2a = document.getElementById("contenido_check_ImporteBruto_2a");
  chib2  = document.getElementById("check_ImporteBruto2");
  
  elib3  = document.getElementById("contenido_check_ImporteBruto_3");
  elib3a = document.getElementById("contenido_check_ImporteBruto_3a");
  chib3  = document.getElementById("check_ImporteBruto3");
     

  switch(nro){
      case 1:
          if(!chib1.checked)   {
            importeBruto2.set(0);
            $('#iva2').val(0);
            totalIva2.set(0);
            subtotal2.set(0);
          }
          if(!chib2.checked)   {
            importeBruto3.set(0);
            $('#iva3').val(0);
            totalIva3.set(0);
            subtotal3.set(0);
          }
          if(!chib3.checked)   {
            importeBruto4.set(0);
            $('#iva4').val(0);
            totalIva4.set(0);
            subtotal4.set(0);
          }
          
          break;
          
      case 2:
          if(!chib2.checked)   {
            importeBruto3.set(0);
            $('#iva3').val(0);
            totalIva3.set(0);
            subtotal3.set(0);

          }
          if(!chib3.checked)   {
            importeBruto4.set(0);
            $('#iva4').val(0);
            totalIva4.set(0);
            subtotal4.set(0);

          }

        break;

      case 3:
          if(!chib3.checked)   {
            importeBruto4.set(0);
            $('#iva4').val(0);
            totalIva4.set(0);
            subtotal4.set(0);

          }

      break;

      case 4:
      break;

      case 0:
      default:
          break;
          
      }


      switch(origen){
        case "bruto":         //leo %IVA y calculo subtotal e IVAtotal
        case "ivaPorcentaje": //leo bruto y calculo Subtotal e IVAtotal

          switch(nro){
            case 1:
              totalIva_1 = ib1*iva1/100;
              sbtot1 = ib1*(1+iva1/100);
              break;
            case 2:
              totalIva_2 = ib2*iva2/100;
              sbtot2 = ib2*(1+iva2/100);
              break;
            case 3:
              totalIva_3 = ib3*iva3/100;
              sbtot3 = ib3*(1+iva3/100);
              break;
            case 4:
              totalIva_4 = ib4*iva4/100;
              sbtot4 = ib4*(1+iva4/100);    
              break;
              }
              
          // console.log("iva total $"+totalIva1);
          // console.log("iva total $"+totalIva2);
          // console.log("iva total $"+totalIva3);
          // console.log("iva total $"+totalIva4);
          
          // console.log("Subtotal 1: "+subtotal1);
          // console.log("Subtotal 2: "+subtotal2);
          // console.log("Subtotal 3: "+subtotal3);
          // console.log("Subtotal 4: "+subtotal4);
          break;
        
        case "totalIva":  //leo bruto y calculo subtotal e %Iva

        switch(nro){

          case 1:
            if(ivatotal_1) iva1 = 100 / ((st1/ivatotal_1)-1);
            else iva1 = 0;
            if(iv_1) bruto1 = ivatotal_1 * 100 / iva1;
            else bruto1 = st1;
            break;
          case 2:
            if(ivatotal_2) iva2 = 100 / ((st1/ivatotal_2)-1);
            else iva2 = 0;
            if(iva2) bruto2 = ivatotal_2 * 100 / iva2;
            else bruto2 = st2;
            break;
          case 3:
            if(ivatotal_3) iva3 = 100 / ((st1/ivatotal_3)-1);
            else iva3 = 0;
            if(iva3) bruto3 = ivatotal_3 * 100 / iva3;
            else bruto3 = st3;
            break;
          case 4:
            if(ivatotal_4) iva4 = 100 / ((st1/ivatotal_4)-1);
            else iva4 = 0;
            if(iva4) bruto4 = ivatotal_4 * 100 / iva4;
            else bruto4 = st4;
            break;

        }
        break;
      }


      //Actualizo variables
      switch(origen){
        case "bruto": //leo %IVA y calculo subtotal e IVAtotal
        case "ivaPorcentaje": //leo bruto y calculo Subtotal e IVAtotal
          
        switch(nro){
          case 1:
            totalIva1.set(totalIva_1);
            subtotal1.set(sbtot1);
            break;
          case 2:
            totalIva2.set(totalIva_2);
            subtotal2.set(sbtot2);
            break;
          case 3:
            totalIva3.set(totalIva_3);
            subtotal3.set(sbtot3);
            break;
          case 4:
            totalIva4.set(totalIva_4);
            subtotal4.set(sbtot4);
            break;
        }


          break;
        case "totalIva":  //leo bruto y calculo subtotal e $Iva
        switch(nro){
          case 1:
            totalIva1.set(totalIva_1);
            importeBruto1.set(bruto1);
            break;
          case 2:
            totalIva2.set(totalIva_2);
            importeBruto2.set(bruto2);
            break;
          case 3:
            totalIva3.set(totalIva_3);
            importeBruto3.set(bruto3);
            break;
          case 4:
            totalIva4.set(totalIva_4);
            importeBruto4.set(bruto4);
            break;
        }
          break;
      }

      let redondeo =  Number.parseFloat($('#redondeo').val());
      let impuestos = Number.parseFloat($('#impuestos').val());
      let impGanancia = Number.parseFloat($('#impuestoGanancia').val());
      let impInterno = Number.parseFloat($('#impuestoInterno').val());

      //Aca puedo llamar a calcular subtotales

      calcular_total();
      // total = subtotal1+subtotal2+subtotal3+subtotal4+redondeo+impuestos;
      // console.log("Total: "+total);


      // $('#total').val(total.toFixed(2));
      // document.getElementById('totalf').innerHTML = total.toFixed(2);

      //CalculoPagosIndividuales(0);
      //Fin Aca ....


}

// Añadir estilo CSS para eliminar el borde de enfoque
const style = document.createElement('style');
style.innerHTML = `
    .no-edit[contenteditable="true"] {
        pointer-events: none;
    }
    .no-focus:focus {
        outline: none;
    }
`;
document.head.appendChild(style);

// Aplicar la clase no-focus a los elementos contenteditable
document.addEventListener('DOMContentLoaded', (event) => {
    const noEditElements = document.querySelectorAll('.no-edit');
    noEditElements.forEach(element => {
        element.classList.add('no-focus');
        element.addEventListener('keydown', (e) => {
            e.preventDefault();
        });
        element.addEventListener('paste', (e) => {
            e.preventDefault();
        });
    });
});

