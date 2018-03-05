<div class="col-md-4">
  <div class="groupData" id="cartData">

    <div id="carWrapper">
    <?php include 'dadosProduto.php';
      $dadosProduto = json_decode(decodificar($_GET['ref'])); ?>
      <table id="cartTable" style="margin: 0 0 -1em 0; ">
        <thead>
          <tr>
            <th class="tableProduto">Descrição</th>
            <th class="tableProduto">Valor</th>
          </tr>
        </thead>
        <tbody>
          <td><?php echo $dadosProduto->name; ?></th>
          <td>R$ <?php echo $dadosProduto->price; ?></th>
        </tbody>
      </table>
    </div>

    <div style="text-align: right"><h3 id="cartTotal"> VALOR Total: R$ <span id="totalValue"><?php echo $dadosProduto->price; ?></span> </h3></div>

  </div>

</div>

