<div class="task">
    <div class="task-heading">
        <h2 id="task-Meet_up_with_Jerry">{TASK_TITLE}</h2>
    </div>
    <div class="task-body">
        <p class="task-time">{TASK_DATE}</p>
    </div>
    <div class="task-description">
        <p class="task-description-text">{TASK_DESCRIPTION}</p>
    </div>
    <form action="folder.php" method="post">
        <input type="hidden" value={TASK_ID} name="taskid">
        <input type="hidden" value="delete" name="action">
        <input type="submit" value="CLOSE" name="submit" class="close-button">
    </form>
</div>