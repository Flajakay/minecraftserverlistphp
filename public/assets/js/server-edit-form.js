document.addEventListener('DOMContentLoaded', function () {
    var protocolSelect = document.getElementById('protocol');
    if (protocolSelect) {
        var steamFields = document.getElementById('editSteamFields');
        var votifierSection = document.getElementById('editVotifierSection');
        var gameSelectorWrapper = document.getElementById('editGameSelectorWrapper');

        function onProtocolChange(protocol) {
            if (steamFields) steamFields.style.display = protocol === 'steam_a2s' ? 'flex' : 'none';
            if (votifierSection) votifierSection.style.display = protocol === 'minecraft_java' ? 'block' : 'none';
            if (gameSelectorWrapper) gameSelectorWrapper.style.display = protocol === 'steam_a2s' ? 'block' : 'none';
        }

        protocolSelect.addEventListener('change', function () {
            onProtocolChange(this.value);
        });

        onProtocolChange(protocolSelect.value);
    }
});
