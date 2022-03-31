window.onload = () =>{
    const FiltresForm = document.querySelector("#filter")

    document.querySelectorAll("#filter input").forEach(input =>{
        input.addEventListener("change",()=> {
            //ont intercepte les clics
            //ont recupere les donnée du form
            const Form = new FormData(FiltresForm);

            //ont fabrique la querty string
            const Params = new URLSearchParams();

              Form.forEach((value, key)=>{
                 Params.append(value, key);
              });

              //ont récupère l'url actif
            const Url = new URL(window.location.href)

            //ont lance la requete ajax
            fetch(Url.pathname + "?" + Params.toString() + "&ajax=1",{
                headers: {
                    "x-Requested-with":"XMLHttpRequest"
                }
            }).then(response => {
                console.log(response)
            }).catch(e=>alert(e));
        });
    })


}
