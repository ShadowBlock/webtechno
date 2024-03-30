<div class="folder">
    <div class="folder-heading">
        <h2 id="folder-team">
            {TITLE}
        </h2>
    </div>
    <div class="folder-preview">
        <h4>Preview:</h4>
        <ul>
            {IF !empty(TASKS)}
                {TASKS}
            {ENDIF}
            {IF NOTHING === true}
            <li>{NOTHING_MESSAGE}</li>
            {ENDIF}
        </ul>
    </div>
    <div class="folder-members">
        <p>Members:</p>
        {MEMBERS}
    </div>
    <form action="folder.php" method="post">
        <input type="hidden" value={FOLDER_ID} name="folderid">
        <input type="submit" value="OPEN" name="submit" class="open-button">
    </form>
</div>
