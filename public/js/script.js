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

    $($(e.currentTarget).parent().parent().next()[0]).slideToggle(300);
  });

  /**
   *
   * adiciona os inputs de parcelamento
   */
  $('input[name="edit_entry_recurrence"]').on("input", (e) => {
    let entryId = $(e.currentTarget)
      .attr("id")
      .replace("edit_installment-", "");

    if (
      $(e.currentTarget).val() == "installment" &&
      $(e.currentTarget).is(":checked") &&
      $(`#installments-${entryId}`).length === 0
    ) {
      var request = $.ajax({
        url: "/value-input-entry_qty_installments",
        method: "POST",
        data: {
          id: entryId,
        },
      });

      request.fail(function (e) {
        console.log(e);
      });
      request.always(function () {
        console.log("complete");
      });
      request.done(function (data) {
        $(e.currentTarget)
          .parent()
          .parent()
          .after(
            `<div style="display: none;" id="installments-${entryId}" class="box-input label-row installments-toggle"><label for="entry_qty_installments-${entryId}" class="input-legend">Quantidade de parcelas</label><input type="number" name="entry_qty_installments-${entryId}" id="entry_qty_installments-${entryId}" value="${
              JSON.parse(data).entry_qty_installments
            }"></div>`
          );

        $(`#installments-${entryId}`).slideToggle(300);
      });
    } else if (
      $(e.currentTarget).parent().parent().next().attr("class") ==
      "box-input label-row installments-toggle"
    ) {
      $(e.currentTarget)
        .parent()
        .parent()
        .next()
        .slideToggle(300, () => {
          $(e.currentTarget).parent().parent().next().remove();
        });
      // console.log($(e.currentTarget).parent().parent().next());
    }
  });

  $('input[name="entry_recurrence"]').on("input", (e) => {
    if (
      $(e.currentTarget).val() == "installment" &&
      $(e.currentTarget).is(":checked")
    ) {
      $(e.currentTarget)
        .parent()
        .parent()
        .after(
          `<div style="display: none;" id="qty-installments" class="box-input label-row installments-toggle"><label for="entry_qty_installments" class="input-legend">Quantidade de parcelas</label><input type="number" name="entry_qty_installments" id="entry_qty_installments" value=""></div>`
        );

      $(`#qty-installments`).slideToggle(300);
    } else if (
      $(e.currentTarget).parent().parent().next().attr("class") ==
      "box-input label-row installments-toggle"
    ) {
      $(e.currentTarget)
        .parent()
        .parent()
        .next()
        .slideToggle(300, () => {
          $(e.currentTarget).parent().parent().next().remove();
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
      .parent() //.box-input.box-toggle
      .parent() //.card-sumary.revenue
      .attr("id")
      .replace("entry-card-", "");

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

  /**
   *
   * evento de exclusão do lançamento
   */
  $(".remove-entry").on("click", (e) => {
    e.preventDefault();

    let entryId = $(e.currentTarget).attr("id").replace("remove-entry-", "");

    var request = $.ajax({
      url: "/remove_entry",
      method: "POST",
      data: {
        id: entryId,
      },
    });

    request.fail(function (e) {
      console.log(e);
    });
    request.always(function () {
      console.log("complete");
    });
    request.done(function () {
      $(`#entry-card-${entryId}`).remove();
    });
  });
}

/**
 *
 *
 * evento de lançamento do cartão de crédito
 */
$("input[name='entry_type']").on("input", (e) => {
  // if ($(e.currentTarget).is(":checked")) {
  if (
    $(e.currentTarget).val() == "credit" &&
    $(e.currentTarget).is(":checked")
  ) {
    $(e.currentTarget).parent().parent().after(`
      <div style="display: none;" id="credit-card-select" class="box-input">
        <select name="entry_credit_card">
          <option value="Inter">Inter</option>
          <option value="Will">Will</option>
          <option value="Mercado Pago">Mercado Pago</option>
        </select>
      </div>
    `);
    $(`#credit-card-select`).slideToggle(300);
  } else if (
    $(e.currentTarget).parent().parent().next().attr("id") ==
    "credit-card-select"
  ) {
    $(e.currentTarget)
      .parent()
      .parent()
      .next()
      .slideToggle(300, () => {
        $(e.currentTarget).parent().parent().next().remove();
      });
  }
});

/**
 *
 *
 * evento de mudança de mês;
 */
$("#entry_month").on("input", (e) => {
  let date = $(e.target).val();

  var request = $.ajax({
    url: "/entry_for_month",
    method: "POST",
    data: {
      date: date,
    },
  });

  request.fail(function (e) {
    console.log(e);
  });
  request.always(function () {
    console.log("complete");
  });
  request.done(function (data) {
    $("#display-entry-sumary")
      .html(data)
      .ready(() => {
        $(".event-key-toggle").on("keydown", (e) => {
          if (e.keyCode == "32") {
            $(e.currentTarget)
              .prev()
              .prop("checked", !$("#effected-yes").prop("checked"));
          }
        });

        $(".edit-entry").on("click", (e) => {
          e.preventDefault();

          $($(e.currentTarget).parent().parent().next()[0]).slideToggle(300);
        });

        /**
         *
         * adiciona os inputs de parcelamento
         */
        $('input[name="edit_entry_recurrence"]').on("input", (e) => {
          let entryId = $(e.currentTarget)
            .attr("id")
            .replace("edit_installment-", "");

          if (
            $(e.currentTarget).val() == "installment" &&
            $(e.currentTarget).is(":checked") &&
            $(`#installments-${entryId}`).length === 0
          ) {
            var request = $.ajax({
              url: "/value-input-entry_qty_installments",
              method: "POST",
              data: {
                id: entryId,
              },
            });

            request.fail(function (e) {
              console.log(e);
            });
            request.always(function () {
              console.log("complete");
            });
            request.done(function (data) {
              $(e.currentTarget)
                .parent()
                .parent()
                .after(
                  `<div style="display: none;" id="installments-${entryId}" class="box-input label-row installments-toggle"><label for="entry_qty_installments-${entryId}" class="input-legend">Quantidade de parcelas</label><input type="number" name="entry_qty_installments-${entryId}" id="entry_qty_installments-${entryId}" value="${
                    JSON.parse(data).entry_qty_installments
                  }"></div>`
                );

              $(`#installments-${entryId}`).slideToggle(300);
            });
          } else if (
            $(e.currentTarget).parent().parent().next().attr("class") ==
            "box-input label-row installments-toggle"
          ) {
            $(e.currentTarget)
              .parent()
              .parent()
              .next()
              .slideToggle(300, () => {
                $(e.currentTarget).parent().parent().next().remove();
              });
            // console.log($(e.currentTarget).parent().parent().next());
          }
        });

        $("div#display-entry-sumary .effected-yes").on("input", (e) => {
          if (!$(e.currentTarget).prop("checked")) {
            $(e.currentTarget).val(0);
          } else {
            $(e.currentTarget).val(1);
          }

          let entryId = $(e.currentTarget)
            .parent() //.box-input.box-toggle
            .parent() //.card-sumary.revenue
            .attr("id")
            .replace("entry-card-", "");

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

        /**
         *
         * evento de exclusão do lançamento
         */
        $(".remove-entry").on("click", (e) => {
          e.preventDefault();

          let entryId = $(e.currentTarget)
            .attr("id")
            .replace("remove-entry-", "");

          var request = $.ajax({
            url: "/remove_entry",
            method: "POST",
            data: {
              id: entryId,
            },
          });

          request.fail(function (e) {
            console.log(e);
          });
          request.always(function () {
            console.log("complete");
          });
          request.done(function () {
            $(`#entry-card-${entryId}`).remove();
          });
        });
      });
  });
});

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
