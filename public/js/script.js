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

      $(e.currentTarget).children().toggleClass("rotate-180deg");
      $(e.currentTarget).next().slideToggle(300);
    });
  }
});
