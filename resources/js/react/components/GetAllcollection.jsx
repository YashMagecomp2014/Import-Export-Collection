import React, { useEffect, useState, useMemo, useCallback } from "react"
import CollectionPage from "./CollectionPage"
import { GlobalAPIcall } from "../config/ApiUtils"
import { Link } from "react-router-dom";
import MaterialReactTable from 'material-react-table';
import { RowSelectionState } from '@tanstack/react-table';
import HistoryList from "./HistoryList";
import { useNavigate } from "@shopify/app-bridge-react";
import CollectionList from "./CollectionList";
import { Button, Popover, ActionList } from '@shopify/polaris';
import { Spinner } from '@shopify/polaris';
import RefreshIcon from '@mui/icons-material/Refresh';
import { IconButton, Tooltip } from '@mui/material';
import { Toast, Frame } from '@shopify/polaris';
import Swal from 'sweetalert2'


function GetAllcollection({ setselectvalue }) {
  const [collections, setUsers] = useState([]);
  const [rowSelection, setRowSelection] = useState([]);
  const [active, setActive] = useState(false);
  const [progress, setProgress] = useState(true);
  const [toastactive, setToastActive] = useState(false);

  const toggleActive = useCallback(() => setActive((active) => !active), []);

  const tosttoggleActive = () => {
    setActive(false);
  }

  const handleImportedAction = async (e) => {
    setToastActive(true);
    var selectedValue = rowSelection;
    const result = Object.keys(selectedValue);

    var data = new FormData();
    data.append("ids", result)
    if (result.length == 0) {
      setToastActive(false);
      Swal.fire({
        icon: 'error',
        title: 'Error...',
        text: 'Please Select Collection',
      })
    }
    var res = await GlobalAPIcall('POST', '/GetSelectedCollections', data);
    setUsers(res);
    fetchData();
    setselectvalue();
    setToastActive(false);



  };

  const handleExportedActions = async (e) => {
    setToastActive(true);

    var selectedValue = rowSelection;
    const result = Object.keys(selectedValue);

    var data = new FormData();
    data.append("ids", result)
    if (result.length == 0) {
      setToastActive(false);
      Swal.fire({
        icon: 'error',
        title: 'Error...',
        text: 'Please Select Collection',
      })
    }
    var res = await GlobalAPIcall('POST', '/GetSelectedCollectionsWithProducts', data);
    setUsers(res);
    fetchData();
    setselectvalue();
    setToastActive(false);



  };


  const activator = (
    <Button onClick={toggleActive} disclosure>
      Actions
    </Button>
  );

  const onclick = () => {
    fetchData();
  }

  const columns = useMemo(
    () => [
      //column definitions...
      {
        accessorKey: 'title',
        header: 'Collection Title',
      },
      {
        accessorKey: 'productsCount',
        header: 'productsCount',
      },
      {
        accessorKey: 'updatedAt',
        header: 'updatedAt',
      },
      {
        accessorKey: 'sortOrder',
        header: 'sortOrder',
      },
      //end
    ],
    [],
  );


  const fetchData = async () => {
    var res = await GlobalAPIcall('GET', '/getallcollection');
    setUsers(res);
    setProgress(false);
  }

  useEffect(() => {
    fetchData()
  }, [])

  useEffect(() => {
    //do something when the row selection changes...
    console.info({ rowSelection });
  }, [rowSelection]);

  return (
    <>


      <div className="row">
        <div className="col-md-5">
          <Popover
            active={active}
            activator={activator}
            autofocusTarget="first-node"
            onClose={toggleActive}
          >
            <Link className="nav-link" aria-current="page" to="/">
              <ActionList
                actionRole="menuitem"
                items={[
                  {
                    content: 'Get All Collection',
                    onAction: handleImportedAction,
                  },
                  {
                    content: 'Get All Collection With Product',
                    onAction: handleExportedActions,
                  },
                ]}
              />
            </Link>
          </Popover>
        </div>
      </div>
      <div className="row">

        <div className="col-md-8">
          {progress && <Spinner accessibilityLabel="Spinner example" size="large" />}
          <MaterialReactTable
            columns={columns}
            data={collections}
            enableRowSelection={true}
            getRowId={(row) => row.id}
            onRowSelectionChange={setRowSelection}
            state={{ rowSelection }}
            enableGlobalFilter={false}
            renderTopToolbarCustomActions={() => (
              <Tooltip arrow title="Refresh Data">
                <IconButton onClick={onclick}>
                  <RefreshIcon />
                </IconButton>
              </Tooltip>
            )}
            muiTableHeadCellProps={{
              //easier way to create media queries, no useMediaQuery hook needed.
              sx: {
                fontSize: {
                  xs: '14px',
                  sm: '15px',
                  md: '16px',
                  lg: '17px',
                  xl: '18px',
                },
              },
            }}
            muiTableBodyCellProps={{
              //easier way to create media queries, no useMediaQuery hook needed.
              sx: {
                fontSize: {
                  xs: '10px',
                  sm: '11px',
                  md: '12px',
                  lg: '13px',
                  xl: '14px',
                },
              },
            }}
          />
        </div>

        <CollectionPage />

      </div>
      {toastactive && <Frame><Toast content="Export File Started" onDismiss={tosttoggleActive} /></Frame>}
    </>
  );
}

export default GetAllcollection;