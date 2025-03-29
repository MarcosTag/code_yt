$(document).ready(() => {
  if (typeof $("#entry_value") != null) {
    $("#entry_value").val("0,00");

    $("#entry_value").on("input", (e) => {
      e.preventDefault();

      let posicaoInicial = $("#entry_value").val().split("");

      posicaoInicial = posicaoInicial.filter((virgula) => virgula !== ",");
      $("#entry_value").val(posicaoInicial.join(""));

      if (posicaoInicial[0] == 0) {
        if (posicaoInicial.length < 4) {
          posicaoInicial.unshift("0");
        }

        posicaoInicial.push(e.key);
        posicaoInicial.shift();
      } else {
        posicaoInicial.push(e.key);
      }

      if (posicaoInicial.length < 4) {
        posicaoInicial.unshift("0");
      }

      posicaoInicial.splice(posicaoInicial.length - 3, 0, ",");

      $("#entry_value").val(posicaoInicial.join(""));
    });

    $(".event-key-toggle").on("keydown", (e) => {
      if (e.keyCode == "32") {
        $(e.currentTarget)
          .prev()
          .prop("checked", !$("#effected-yes").prop("checked"));
      }
    });

    /**
     *
     * evento accordeon
     */
    $(".accordeon-open").on("click", (e) => {
      e.preventDefault();

      $($(e.currentTarget).children()[1]).toggleClass("rotate-180deg");
      $(e.currentTarget).next().slideToggle(300);
    });

    // $("#installment").on("input", (e) => {
    //   if ($("#installments").length === 0) {
    //     $(e.currentTarget)
    //       .parent()
    //       .parent()
    //       .after("<div id='installments'>TESTE</div>");
    //   }
    // });

    /**
     *
     * adiciona os inputs de parcelamento
     */
    $('input[name="entry_recurrence"]').on("input", (e) => {
      if ($("#installment").is(":checked") && $("#installments").length === 0) {
        $("#installment")
          .parent()
          .parent()
          .after(
            '<div style="display: none;" id="installments" class="box-input label-row"><label for="entry_qty_installments" class="input-legend">Quantidade de parcelas</label><input type="number" name="entry_qty_installments" id="entry_qty_installments"></div>'
          );

        $("#installments").slideToggle(300);
      } else {
        $("#installments").slideToggle(300, () => {
          $("#installments").remove();
        });
      }
    });
  }
});
