
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script src="{{ asset('js/ml.js') }}"></script>

<script>
  
$(function() {
    $('select[name=proveedor]').change(function() {
      let prov = $('#proveedor').val();
      //console.log(prov);
      //alert(prov);

      @foreach($proveedores as $p)
      if(prov == {{ $p->nro_proveedor}}){  
        console.log('{{ $p->rubro->nombre}}');
        console.log('se ejecuta cuando cambia proveedor');
        $('#rubro').val({{ $p->rubro->valor }});
        $('#condicionIVA').val({{ $p->iva_r->valor }});
        
        switch({{  $p->iva_r->valor  }}){
          case 0:
          case 3:
          case 4:
          case 6:  
              $('#iva1').val(0);
              break;
              
          case 1:
          case 2:
              $('#iva1').val(21);
              break;
            
          default:
              $('#iva1').val(21);
              break;
        }

      }
      @endforeach
      calcular_total(); //recalculo el total de los importes
      });
  });

 

</script>

