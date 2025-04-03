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
     * eventos accordeon
     */
    $(".accordeon-open").on("click", (e) => {
      e.preventDefault();

      $($(e.currentTarget).children()[1]).toggleClass("rotate-180deg");
      $(e.currentTarget).next().slideToggle(300);
    });

    $(".edit-entry").on("click", (e) => {
      e.preventDefault();

      console.log($(e.currentTarget).parent().parent().next());

      // $($(e.currentTarget).parent().parent().next()[0]).toggleClass("rotate-180deg");
      $($(e.currentTarget).parent().parent().next()[0]).slideToggle(300);
    });

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

    $("div#display-entry-sumary .effected-yes").on("input", (e) => {
      if (!$(e.currentTarget).prop("checked")) {
        $(e.currentTarget).val(0);
      } else {
        $(e.currentTarget).val(1);
      }

      let entryId = $(e.currentTarget)
        .parent()
        .parent()
        .parent()
        .parent()
        .attr("id");

      if ($(e.currentTarget).val() == 0 || $(e.currentTarget).val() == 1) {
        var entryVal = $(e.currentTarget).val();
      } else {
        var entryVal = 0;
      }

      var request = $.ajax({
        url: "/up_entry_effected",
        method: "POST",
        data: {
          id: entryId,
          val: entryVal,
        },
      });

      // request.done(function (msg) {
      //   console.log(msg);
      // });

      // request.fail(function (jqXHR, textStatus) {
      //   console.log(jqXHR);
      // });

      request.fail(function (e) {
        console.log(e);
      });
      request.always(function () {
        console.log("complete");
      });
      request.done(function () {
        console.log("success");
      });
    });

    $(".input-entry-value").on("input", (event) => {
      field_effect_input_value($(event.currentTarget), event);
      //console.log(event.key);
    });
  }

  function field_effect_input_value(element, event) {
    event.preventDefault();
    let posicaoInicial = $(element).val().split("");

    posicaoInicial = posicaoInicial.filter((virgula) => virgula !== ",");
    element.val(posicaoInicial.join(""));

    if (posicaoInicial[0] == 0) {
      if (posicaoInicial.length < 4) {
        posicaoInicial.unshift("0");
      }

      posicaoInicial.push(event.key);
      posicaoInicial.shift();
    } else {
      posicaoInicial.push(event.key);
    }

    if (posicaoInicial.length < 4) {
      posicaoInicial.unshift("0");
    }

    posicaoInicial.splice(posicaoInicial.length - 3, 0, ",");

    element.val(posicaoInicial.join(""));
  }
});
