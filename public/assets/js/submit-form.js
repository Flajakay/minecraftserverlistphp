document.addEventListener('DOMContentLoaded', function () {
    var protocolSelect = document.getElementById('protocol');
    var portInput = document.getElementById('port');
    var portDefaultText = document.getElementById('portDefaultText');
    var gameSelectorWrapper = document.getElementById('gameSelectorWrapper');
    var votifierSection = document.getElementById('votifierSection');
    var nameInput = document.getElementById('name');

    function onProtocolChange(protocol) {
        if (protocol === 'steam_a2s') {
            portInput.value = portInput.value || '27015';
            portInput.placeholder = '27015';
            portDefaultText.textContent = 'Default: 27015';
            gameSelectorWrapper.style.display = 'block';
            votifierSection.style.display = 'none';
            nameInput.placeholder = 'My Awesome Game Server';
        } else {
            portInput.value = portInput.value || '25565';
            portInput.placeholder = '25565';
            portDefaultText.textContent = 'Default: 25565';
            gameSelectorWrapper.style.display = 'none';
            votifierSection.style.display = 'block';
            nameInput.placeholder = 'My Awesome Minecraft Server';
        }
    }

    protocolSelect.addEventListener('change', function () {
        onProtocolChange(this.value);
    });

    onProtocolChange(protocolSelect.value);
});
