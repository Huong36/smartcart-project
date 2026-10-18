document.querySelectorAll('.flash-toast').forEach((element) => {
    bootstrap.Toast.getOrCreateInstance(element, { animation: false }).show();
});
